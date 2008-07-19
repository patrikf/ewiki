<?php include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->get_name()); ?></h1>
<form id="edit-form" method="post" action="<?= $page->get_url() ?>?action=edit&commit=<?= $commit ?>">
<textarea name="content"><?= Markup::escape($content) ?></textarea>
<p>Summary of changes:
<input type="text" name="summary" class="summary" /></p>
<input type="submit" value="Save changes" class="submit" />
</form>
<?php include('footer.php'); ?>
