<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title><?= Config::WIKI_NAME ?>: <?= Markup::escape($title) ?></title>
<link rel="stylesheet" href="<?= Config::PATH . '/templates/' . Config::TEMPLATE . '/style.css' ?>" />
<script type="text/javascript" src="<?= Config::PATH ?>/mootools-1.2-core-nc.js"></script>
<script type="text/javascript" src="<?= Config::PATH ?>/templates/<?= Config::TEMPLATE ?>/site.js"></script>
</head>
<body>
<div id="page">
<div id="linkpane">
    <div id="globallinks">
        <a href="<?= Config::PATH ?>/">home</a>
        <a href="<?= Config::PATH ?>/:recent">recent changes</a>
    </div>
    <? if (isset($page)): ?>
        <div id="pagelinks">
           <a href="<?= $page->getURL() ?>"<?= $action == 'view' ? ' class="active"' : '' ?>>view</a>
           <a href="<?= $page->getURL() ?>?action=edit"<?= $action == 'edit' ? ' class="active"' : '' ?>><?= Config::ALLOW_EDIT ? 'edit' : 'source' ?></a>
           <a href="<?= $page->getURL() ?>?action=history"<?= $action == 'history' ? ' class="active"' : '' ?>>history</a>
        </div>
    <? endif; ?>
    <div style="clear: both"></div>
</div>
