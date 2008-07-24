<?php $title = $page->getName(); ?>
<?php include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->getName()) ?></h1>
<?= $page->format() ?>
<?php include('footer.php');
