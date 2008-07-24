<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title><?= Config::WIKI_NAME ?>: <?= Markup::escape($page->getName()) ?></title>
<link rel="stylesheet" href="<?= Config::PATH . '/templates/' . Config::TEMPLATE . '/style.css' ?>" />
<script src="<?= Config::PATH ?>/mootools-1.2-core-nc.js"></script>
<script src="<?= Config::PATH ?>/templates/<?= Config::TEMPLATE ?>/site.js"></script>
</head>
<body>
<div id="page">
<div id="linkpane">
    <div id="globallinks">
        <a href="<?= Config::PATH ?>/">home</a>
    </div>
    <div id="pagelinks">
    <?php foreach (array('view', 'edit', 'history') as $i): ?>
        <a href="<?= $page->getURL() ?><?= $i == 'view' ? '' : '?action='.$i ?>"<?= $i == $action ? ' class="active"' : '' ?>><?= $i ?></a>
    <?php endforeach; ?>
    </div>
    <div style="clear: both"></div>
</div>
