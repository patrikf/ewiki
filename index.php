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

$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action == '')
    $action = 'view';

$is_head = !isset($_GET['commit']);
if ($is_head)
    $commit = $repo->getHead(Config::GIT_BRANCH);
else
    $commit = sha1_bin($_GET['commit']);
$commit = $repo->getObject($commit);

$page = WikiPage::from_url($parts[0], $commit);

$view = new View;
$view->page = $page;
$view->action = $action;
$view->commit_id = sha1_hex($commit->getName());

if ($action == 'view') // {{{1
{
    switch ($page->get_page_type())
    {
        case WikiPage::TYPE_PAGE:
            $view->set_template('page-view.php');
            break;
        case WikiPage::TYPE_IMAGE:
            $view->set_template('page-view-image.php');
            break;
        case WikiPage::TYPE_BINARY:
            $view->set_template('page-view-binary.php');
            break;
    }
    $view->display();
}
else if ($action == 'history') // {{{1
{
    $view->set_template('page-history.php');

    $history = array();
    $commits = array($repo->getHead(Config::GIT_BRANCH));
    while (($commit = array_shift($commits)) !== NULL)
    {
	$commit = $repo->getObject($commit);
	$commits += $commit->parents;

	$entry = new stdClass;
	array_push($history, $entry);
	$entry->commit = $commit->getName();
	try
	{
	    $entry->blob = WikiPage::find_page($commit, $page->path)->getName();
	}
	catch (InvalidPageError $e)
	{
	    $entry->blob = NULL;
	}
	$entry->summary = $commit->summary;
	$entry->author = $commit->author->name;
	$entry->time = $commit->committer->time;
    }
    usort($history, create_function('$a,$b', 'return ($b->time - $a->time);'));
    $oldblob = NULL;
    for ($i = count($history)-1; $i >= 0; $i--)
    {
	if ($history[$i]->blob == $oldblob)
	    array_splice($history, $i, 1);
	else
	    $oldblob = $history[$i]->blob;
    }
    foreach ($history as $entry)
    {
	$entry->commit = sha1_hex($entry->commit);
	$entry->blob = sha1_hex($entry->blob);
    }
    $view->history = $history;

    $view->display();
}
else if ($action == 'edit') // {{{1
{
    if (isset($_POST['content'])) // {{{1
    {
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

	$newcommit->summary = $_POST['summary'];
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
        redirect($page->get_url());
    } /// }}}1

    $view->set_template('page-edit.php');
    $view->new = ($page->object === NULL);
    if (isset($content))
	$view->content = $content;
    else
	$view->content = ($view->new ? '' : $page->object->data);

    $view->display();
}
else if ($action == 'get') // {{{1
{
    header('Content-Type: '.$page->get_mime_type());
    header('Content-Disposition: inline; filename="' . addcslashes($page->get_name(), '"') . '"');
    echo $page->object->data;
}
else if ($action == 'image') // {{{ 1
{
    assert($page->get_page_type() == WikiPage::TYPE_IMAGE);
    header('Content-Type: '.$page->get_mime_type());
    header('Content-Disposition: inline; filename="' . addcslashes($page->get_name(), '"') . '"');

    if (isset($_GET['width']) || isset($_GET['height']))
    {
        // Resize (oh god why does php not have a simple image_resize function?)
        $new_size = array((int)$_GET['width'], (int)$_GET['width']);
        if ($new_size[0] < 10)
            $new_size[0] = Config::IMAGE_WIDTH;
        if ($new_size[1] < 10)
            $new_size[1] = Config::IMAGE_HEIGHT;
        $old_image = imagecreatefromstring($page->object->data);
        $old_size = array(imagesx($old_image), imagesy($old_image));
        $factor = min($new_size[0] / $old_size[0], $new_size[1] / $old_size[1]); // Keep aspect ratio
        if ($factor < 1)
        {
            $new_size = array((int)$old_size[0] * $factor, (int)$old_size[1] * $factor);
            $new_image = imagecreatetruecolor($new_size[0], $new_size[1]);
            imagecopyresampled($new_image, $old_image, 0, 0, 0, 0, $new_size[0], $new_size[1], $old_size[0], $old_size[1]);
            imagedestroy($old_image);
        }
        else
            $new_image = $old_image; // No resize if image already has the right size or is smaller than wanted size
        unset($factor, $old_size, $new_size, $old_image);

        // Send the image
        switch ($page->get_mime_type())
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
        }
        imagedestroy($new_image);
    }
    else
            echo $page->object->data;

} // }}}1

/* vim:set fdm=marker fmr={{{,}}}: */

?>
