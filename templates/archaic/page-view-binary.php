<?php include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->get_name()) ?></h1>
<p><a href="<?= $page->get_url() ?>?action=get">Download “<?= Markup::escape($page->get_name()) ?>”</a></p>
<?php include('footer.php');
