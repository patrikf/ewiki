<?php

error_reporting(E_ALL | E_STRICT);
assert_options(ASSERT_BAIL, TRUE);
setlocale(LC_ALL, 'en_GB.UTF-8');
date_default_timezone_set('Europe/Vienna');

require_once('git.php');
require_once('markup.php');
require_once('wikipage.php');
require_once('view.php');
require_once('pfcore-tiny.php');

$repo = new Git('/srv/patrik/ewiki.git');

$parts = explode('?', $_SERVER['REQUEST_URI'], 2);

$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action == '')
    $action = 'view';

if (isset($_GET['commit']))
    $commit = sha1_bin($_GET['commit']);
else
    $commit = $repo->getHead('master');
$commit = $repo->getObject($commit);

$page = WikiPage::from_url($parts[0], $commit);

if ($action == 'view')
{
    $view = new View('views/page-view.php');
    $view->page = $page;
    $view->display();
}
else if ($action == 'history')
{
    $view = new View('views/page-history.php');
    $view->page = $page;

    $history = array();
    $commits = array($repo->getHead('master'));
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
else if ($action == 'edit')
{
    $view = new View('views/page-edit.php');

    if (isset($_POST['content']))
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
		array_unshift($pending_refs, array($cur, $obj));
	    }
	    else
	    {
		$cur = $obj->nodes[$part];
		$obj = clone $repo->getObject($cur->object);
	    }
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
	$stamp->name = 'Patrik Fimml';
	$stamp->email = 'patrik@fimml.at';
	$stamp->time = time();
	$stamp->offset = idate('Z', $stamp->time);

	$newcommit->author = $stamp;
	$newcommit->committer = $stamp;

	$newcommit->summary = $_POST['summary'];
	$newcommit->detail = '';
	$newcommit->rehash();

	/* now, try to atomically fast-forward master branch */
	$f = fopen(sprintf('%s/refs/heads/%s', $repo->dir, 'master'), 'a+');
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
    }

    $view->commit = sha1_hex($commit->getName());
    $view->new = ($page->object === NULL);
    $view->page = $page;
    if (isset($content))
    {
	$view->content = $content;
    }
    else
	$view->content = ($view->new ? '' : $page->object->data);

    $view->display();
}

?>
