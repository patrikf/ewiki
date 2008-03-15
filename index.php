<?php

error_reporting(E_ALL | E_STRICT);
assert_options(ASSERT_BAIL, TRUE);

$parts = explode('?', $_SERVER['REQUEST_URI'], 2);
$page = substr(urldecode($parts[0]), strlen('/ewiki/'));
$query = isset($parts[1]) ? $parts[1] : '';

$repo = new Git('/home/patrik/git/ewiki');
$tree = $repo->getObject($repo->getHead('master'));

?>
