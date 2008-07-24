<?php $title = $page->getName(); ?>
<?php include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->getName()) ?></h1>
<p><a href="<?= $page->getURL() ?>?action=get&amp;commit=<?= $commit_id ?>">Download “<?= Markup::escape($page->getName()) ?>”</a></p>
<?php include('footer.php');
