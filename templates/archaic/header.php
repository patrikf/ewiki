<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title><?= Config::WIKI_NAME ?>: <?= Markup::escape($title) ?></title>
<link rel="stylesheet" href="<?= Config::PATH . '/templates/' . Config::TEMPLATE . '/style.css' ?>" />
<script type="text/javascript" src="<?= Config::PATH ?>/mootools-1.2-core-nc.js"></script>
<script type="text/javascript" src="<?= Config::PATH ?>/templates/<?= Config::TEMPLATE ?>/site.js"></script>
<meta name="date" content="<?= date('r', (isset($page) ? $page->getLastModified() : time())) ?>" />
<? if (isset($page)): ?>
<? if ($action == 'edit'): ?>
<meta name="robots" content="noindex,follow" />
<? endif; ?>
<? if (Config::ALLOW_EDIT): ?>
<link rel="alternate" type="application/x-wiki" title="Edit this page!" href="<?= $page->getUrl() ?>?action=edit" />
<? endif; ?>
<? endif; ?>
<? if (isset($recent_changes_feeds)): ?>
<link rel="alternate" type="application/rss+xml" title="Recent changes (RSS 2.0)" href="<?= Config::PATH ?>/:rss20" />
<? endif; ?>
</head>
<body>
<div id="page">
<div id="linkpane">
    <? if ($user): ?>
    <div id="userinfo">
        <span id="uid"><?= Markup::escape($user->name) ?>
        &lt;<?= Markup::escape($user->email) ?>&gt;</span>
        <a href="<?= Config::PATH ?>/:profile">change password/email</a>
        <a href="<?= Config::PATH ?>/:logout">logout</a>
    </div>
    <? endif; ?>
    <? if (!Config::REQUIRE_LOGIN || $user): ?>
    <div id="globallinks">
        <a href="<?= Config::PATH ?>/">home</a>
        <a href="<?= Config::PATH ?>/:recent">recent changes</a>
        <a href="<?= Config::PATH ?>/:conflicts">unmerged conflicts (<?= $n_conflicts ?>)</a>
    </div>
    <? if (isset($page)): ?>
        <div id="pagelinks">
        <? foreach (array('view' => 'view', 'edit' => (Config::ALLOW_EDIT ? 'edit' : 'source'), 'history' => 'history') as $i => $label): ?>
            <a
                href="<?= $page->getURL() ?><?= $i == 'view' ? '' : '?action='.$i ?>"
                <?= $i == $action ? ' class="active"' : '' ?>>
                <?= $label ?>
            </a>
        <? endforeach; ?>
        </div>
    <? endif; ?>
    <div style="clear: both"></div>
    <form method="post" action="<?= Config::PATH ?>/:find">
        <div id="searchbox">
            <input name="q" class="text" value="search (beta)" />
        </div>
    </form>
    <? endif; ?>
</div>
