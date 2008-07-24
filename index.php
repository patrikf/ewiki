<?php

error_reporting(E_ALL | E_STRICT);

set_include_path('include/');
require_once('core.class.php');
require_once('config.class.php');

setlocale(LC_ALL, Config::LOCALE);
date_default_timezone_set(Config::TIMEZONE);

require_once('git/git.class.php');
require_once('markup.class.php');
require_once('wikipage.class.php');
require_once('view.class.php');
require_once('mime.class.php');

function redirect($uri)
{
    header('HTTP/1.1 303 See Other');
    header('Location: '.$uri);
}

$repo = new Git(Config::GIT_PATH);

$parts = explode('?', $_SERVER['REQUEST_URI'], 2);
assert(!strncmp($parts[0], Config::PATH, strlen(Config::PATH)));
$parts[0] = substr($parts[0], strlen(Config::PATH));

$view = new View;

$is_head = !isset($_GET['commit']);
if ($is_head)
    $commit = $repo->getHead(Config::GIT_BRANCH);
else
    $commit = sha1_bin($_GET['commit']);
$commit = $repo->getObject($commit);
$commit_id = sha1_hex($commit->getName());
$view->commit_id = $commit_id;

$special = $page = NULL;
if (!strncmp($parts[0], '/:', 2))
    $special = substr($parts[0], 2);
else
{
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    if ($action == '')
        $action = 'view';
    $view->action = $action;

    $page = WikiPage::fromURL($parts[0], $commit);
    $view->page = $page;
}

if ($special == 'recent') // {{{1
{
    $view->setTemplate('recent-changes.php');

    $commits = array();
    $history = array_reverse($commit->getHistory());
    for ($i = 0; $i < min(10, count($history)); $i++)
    {
        $cur = $history[$i];

        $commits[$i] = new stdClass;
        $commits[$i]->commit_id = sha1_hex($cur->getName());
        $commits[$i]->summary = $cur->summary;
        $commits[$i]->detail = $cur->detail;

        if ($i+1 < count($history))
            $prev = $history[$i+1];
        else
            $prev = NULL;

        $prev_files = $prev ? $prev->repo->getObject($prev->tree)->listRecursive() : array();
        $cur_files = $cur->repo->getObject($cur->tree)->listRecursive();
        $changes = array();

        sort($prev_files);
        sort($cur_files);
        $a = $b = 0;
        while ($a < count($prev_files) || $b < count($cur_files))
        {
            if ($a < count($prev_files) && $b < count($cur_files))
                $cmp = strcmp($prev_files[$a], $cur_files[$b]);
            else
                $cmp = 0;
            $change = new stdClass;
            if ($b >= count($cur_files) || $cmp < 0)
            {
                $change->type = 'removed';
                $change->subject = $prev_files[$a];
                array_push($changes, $change);
                $a++;
            }
            else if ($a >= count($prev_files) || $cmp > 0)
            {
                $change->type = 'added';
                $change->subject = $cur_files[$b];
                array_push($changes, $change);
                $b++;
            }
            else
            {
                if ($prev->find($prev_files[$a]) != $cur->find($cur_files[$b]))
                {
                    $change->type = 'modified';
                    $change->subject = $prev_files[$a];
                    array_push($changes, $change);
                }
                $a++;
                $b++;
            }
        }
        foreach ($changes as $change)
        {
            $page = new WikiPage($change->subject);
            $change->subject_url = $page->getURL();
        }
        $commits[$i]->changes = $changes;
    }
    $view->commits = $commits;

    $view->display();
}
else if ($special !== NULL) // {{{1
    throw new Exception(sprintf('unknown special: %s', $special));
else if ($action == 'view') // {{{1
{
    switch ($page->getPageType())
    {
        case WikiPage::TYPE_PAGE:
            $view->setTemplate('page-view.php');
            break;
        case WikiPage::TYPE_IMAGE:
            $view->setTemplate('page-view-image.php');
            break;
        case WikiPage::TYPE_BINARY:
            $view->setTemplate('page-view-binary.php');
            break;
        case WikiPage::TYPE_TREE:
            $view->setTemplate('page-view-tree.php');

            $view->entries = array();
            foreach ($page->listEntries() as $entry)
            {
                $obj = new stdClass;
                $obj->url = $entry->getURL() . ($is_head ? '' : '?commit='.$commit_id);
                $obj->name = $entry->getName();
                array_push($view->entries, $obj);
            }
            break;
        case NULL:
            $view->setTemplate('page-new.php');
            $view->has_history = !!count($page->getPageHistory());
            break;
    }
    $view->display();
}
else if ($action == 'history') // {{{1
{
    $view->setTemplate('page-history.php');

    $history = $page->getPageHistory();
    foreach ($history as $entry)
    {
        $entry->summary = $entry->commit->summary;
        $entry->author = $entry->commit->author->name;
        $entry->time = $entry->commit->committer->time;
	$entry->commit = sha1_hex($entry->commit->getName());
	$entry->blob = $entry->blob ? sha1_hex($entry->blob->getName()) : NULL;
    }
    $view->history = array_reverse($history);

    $view->display();
}
else if ($action == 'edit') // {{{1
{
    if (isset($_POST['content'])) // {{{2
    {
        if ($_POST['type'] == 'file')
            $content = file_get_contents($_FILES['file']['tmp_name']);
        else
            $content = str_replace("\r", '', str_replace("\r\n", "\n", $_POST['content']));

	/* first, create all new objects in memory */
	/* pending: contains all objects that need to be written */
	$pending = array();

	$blob = new GitBlob($repo);
	array_push($pending, $blob);
	$blob->data = $content;
	$blob->rehash();

	$tree = clone $repo->getObject($commit->tree);
	$cur = NULL;
	$obj = $tree;
	/* pending_refs: allows us to reference objects that we modify in the
	 *               future (dirty solution) */
	$pending_refs = array();
	foreach ($page->path as $part)
	{
	    array_push($pending, $obj);
	    if (!isset($obj->nodes[$part]))
	    {
		$cur = $obj->nodes[$part] = new stdClass;
		$cur->mode = 040000;
		$cur->name = $part;
		$obj = new GitTree($repo);
	    }
	    else
	    {
		$cur = $obj->nodes[$part];
		$obj = clone $repo->getObject($cur->object);
	    }
	    array_unshift($pending_refs, array($cur, $obj));
	}
	array_shift($pending_refs); /* we're overwriting this saved tree */
	$cur->mode = 0100640;
	$cur->object = $blob->getName();
	foreach ($pending_refs as $ref)
	{
	    $ref[1]->rehash();
	    $ref[0]->object = $ref[1]->getName();
	}
	$tree->rehash();

	$newcommit = new GitCommit($repo);
	array_push($pending, $newcommit);
	$newcommit->tree = $tree->getName();
	$newcommit->parents = array($commit->getName());
	$stamp = new GitCommitStamp;
	$stamp->name = $_SERVER['REMOTE_ADDR'];
	$stamp->email = sprintf('anonymous@%s', $_SERVER['REMOTE_ADDR']);
	$stamp->time = time();
	$stamp->offset = idate('Z', $stamp->time);

	$newcommit->author = $stamp;
	$newcommit->committer = $stamp;

	$newcommit->summary = sprintf('%s: %s', $page->getName(), $_POST['summary']);
	$newcommit->detail = '';
	$newcommit->rehash();

	/* now, try to automatically fast-forward configured branch */
	$f = fopen(sprintf('%s/refs/heads/%s', $repo->dir, Config::GIT_BRANCH), 'a+');
	flock($f, LOCK_EX);
	$ref = stream_get_contents($f);
	if (strlen($ref) == 0 || sha1_bin($ref) == $commit->getName())
	{
	    foreach ($pending as $obj)
		$obj->write();
	    ftruncate($f, 0);
	    fwrite($f, sha1_hex($newcommit->getName()));
	}
	else
	{
	    throw new Exception('fast-forward merge not possible');
	}
	fclose($f);
        redirect($page->getURL());
    }
    else // {{{2
    {
        $view->setTemplate('page-edit.php');
        $view->page_type = $page->getPageType();
        $view->is_binary = $view->page_type == WikiPage::TYPE_BINARY || $view->page_type == WikiPage::TYPE_IMAGE;
        if (isset($content))
            $view->content = $content;
        else
            $view->content = ($view->page_type == WikiPage::TYPE_PAGE ? $page->object->data : '');

        $view->display();
    } // }}}2
}
else if ($action == 'get') // {{{1
{
    header('Content-Type: '.$page->getMimeType());
    header('Content-Disposition: inline; filename="' . addcslashes($page->getName(), '"') . '"');
    header('Content-Length: '.strlen($page->object->data));
    echo $page->object->data;
}
else if ($action == 'image') // {{{1
{
    assert($page->getPageType() == WikiPage::TYPE_IMAGE);
    header('Content-Type: '.$page->getMimeType());
    header('Content-Disposition: inline; filename="' . addcslashes($page->getName(), '"') . '"');

    if (isset($_GET['width']) || isset($_GET['height']))
    {
        // Resize (oh god why does php not have a simple image_resize function?)
        $old_image = imagecreatefromstring($page->object->data);
        $old_size = array(imagesx($old_image), imagesy($old_image));
        $new_size = array((int)$_GET['width'], (int)$_GET['width']);
        if (!$new_size[0])
            $new_size[0] = $old_size[0];
        if (!$new_size[1])
            $new_size[1] = $old_size[1];
        $factor = min($new_size[0] / $old_size[0], $new_size[1] / $old_size[1]); // Keep aspect ratio
        if ($factor < 1)
        {
            $new_size = array((int)($old_size[0] * $factor), (int)($old_size[1] * $factor));
            $new_image = imagecreatetruecolor($new_size[0], $new_size[1]);
            imagecopyresampled($new_image, $old_image, 0, 0, 0, 0, $new_size[0], $new_size[1], $old_size[0], $old_size[1]);
            imagedestroy($old_image);
        }
        else
            $new_image = $old_image; // No resize if image already has the right size or is smaller than wanted size

        // Send the image
        switch ($page->getMimeType())
        {
            case 'image/gif':
                imagegif($new_image);
                break;
            case 'image/jpeg':
                imagejpeg($new_image, null, 100);
                break;
            case 'image/png':
                imagepng($new_image, null, 9);
                break;
            case 'image/vnd.wap.wbmp':
                imagewbmp($new_image);
                break;
            case 'image/x-xbitmap':
                imagexbm($new_image);
                break;
            default:
                throw new Exception(sprintf('unhandled image type: %s', $page->getMimeType()));
        }
        imagedestroy($new_image);
    }
    else
        echo $page->object->data;

} // }}}1
else
    throw new Exception(sprintf('unhandled action: %s', $action));

/* vim:set fdm=marker fmr={{{,}}}: */

?>
