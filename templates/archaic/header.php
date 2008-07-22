<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<!DOCTYPE html PUBLIC "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?= Config::WIKI_NAME ?>: <?= Markup::escape($page->get_name()) ?></title>
<link rel="stylesheet" href="<?= Config::PATH . '/templates/' . Config::TEMPLATE . '/style.css' ?>" />
</head>
<body>
<div id="page">
<div id="linkpane">
    <div id="globallinks">
        <a href="<?= Config::PATH ?>/">home</a>
    </div>
    <div id="pagelinks">
    <?php foreach (array('view', 'edit', 'history') as $i): ?>
        <a href="<?= $page->get_url() ?><?= $i == 'view' ? '' : '?action='.$i ?>"<?= $i == $action ? ' class="active"' : '' ?>><?= $i ?></a>
    <?php endforeach; ?>
    </div>
    <div style="clear: both"></div>
</div>
