<? $title = $page->getName(); ?>
<? include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->getName()) ?></h1>
<? foreach ($entries as $entry): ?>
    <p><a href="<?= $entry->url ?>"><?= Markup::escape($entry->name) ?></a></p>
<? endforeach; ?>
<? include('footer.php');
