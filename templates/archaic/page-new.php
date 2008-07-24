<?php include('header.php'); ?>
<h1 id="pagetitle" class="new"><?= Markup::escape($page->getName()) ?></h1>
<p>
<? if ($has_history): ?>
This page does not exist any more.
You can <a href="<?= $page->getURL() ?>?action=edit" class="new">create it now</a>
or <a href="<?= $page->getURL() ?>?action=history">look at its history</a> to see its previous contents.
<? else: ?>
This page does not exist yet.
You can <a href="<?= $page->getURL() ?>?action=edit" class="new">create it now</a>.
<? endif; ?>
</p>
<?php include('footer.php');
