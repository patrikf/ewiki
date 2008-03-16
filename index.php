<?php

error_reporting(E_ALL | E_STRICT);
assert_options(ASSERT_BAIL, TRUE);

require_once('git.php');

$parts = explode('?', $_SERVER['REQUEST_URI'], 2);
$page = substr(urldecode($parts[0]), strlen('/ewiki/'));
$query = isset($parts[1]) ? $parts[1] : '';

$repo = new Git('/home/patrik/git/ewiki/.git');
$head = $repo->getObject($repo->getHead('master'));

$path = explode('/', $page);
$cur = $repo->getObject($head->tree);
while (count($path) && $path[0] != '')
{
    if ($cur->getType() != Git::OBJ_TREE)
	die('Not a tree');
    if (!isset($cur->nodes[$path[0]]))
	die('Not found');
    $cur = $repo->getObject($cur->nodes[array_shift($path)]->object);
}

if (count($path))
{
    /* directory view */
}
else
{
    header('Content-type: text/plain');
    echo $cur->data;
}

?>
