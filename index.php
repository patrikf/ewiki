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

?>
