<?php include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->get_name()) ?></h1>
<?php foreach ($entries as $entry): ?>
    <p><a href="<?= $entry->url ?>"><?= Markup::escape($entry->name) ?></a></p>
<?php endforeach; ?>
<?php include('footer.php');
