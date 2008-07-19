<?php include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->get_name()) ?></h1>
<?= $page->format() ?>
<?php include('footer.php');
