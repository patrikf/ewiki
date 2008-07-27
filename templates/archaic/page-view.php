<? $title = $page->getName(); ?>
<? require('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->getName()) ?></h1>
<? if (!$commit_is_tip): ?>
<div class="history-warning">
You are currently viewing an old version (commit
<span class="sha1"><?= $commit_id ?></span>) of this page. The
<a href="<?= $page->getURL() ?>">current version</a> might show
significant differences.
</div>
<? endif; ?>
<?php

$map = array(
        WikiPage::TYPE_PAGE => 'text',
        WikiPage::TYPE_BINARY => 'binary',
        WikiPage::TYPE_IMAGE => 'image',
        WikiPage::TYPE_TREE => 'tree',
        NULL => 'new'
    );

require('page-view-'.$map[$type].'.php');

?>
<? require('footer.php'); ?>
