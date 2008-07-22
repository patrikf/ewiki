<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<!DOCTYPE html PUBLIC "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?= Config::WIKI_NAME ?>: <?= Markup::escape($page->get_name()) ?></title>
<link rel="stylesheet" href="<?= Config::PATH . '/templates/' . Config::TEMPLATE . '/style.css' ?>" />
</head>
<body>
<div id="head">
</div>
<div id="page">
<?php if(isset($page)): ?>
<div id="pagelinks">
    <a href="<?= $page->get_url() ?>">view</a>
    <a href="<?= $page->get_url() ?>?action=edit" class="active">edit</a>
    <a href="<?= $page->get_url() ?>?action=history">history</a>
</div>
<?php endif; ?>
