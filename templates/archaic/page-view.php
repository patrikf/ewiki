<? $title = $page->getName(); ?>
<? require('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->getName()) ?></h1>
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
