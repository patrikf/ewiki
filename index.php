<?php

error_reporting(E_ALL | E_STRICT);
assert_options(ASSERT_BAIL, TRUE);

require_once('git.php');
require_once('markup.php');
require_once('wikipage.php');
require_once('view.php');

$repository = new Git('/home/patrik/git/ewiki/.git');

$parts = explode('?', $_SERVER['REQUEST_URI'], 2);
$page = WikiPage::from_url($parts[0]);

$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action == '')
    $action = 'view';

if ($action == 'view')
{
    header('Content-type: text/html');
    $view = new View('views/page-view.php');
    $view->page = $page;
    $view->display();
}
else if ($action == 'history')
{
}

?>
