<?php include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->getName()); ?></h1>
<form id="edit-form" accept-charset="UTF-8" method="post" action="<?= $page->getURL() ?>?action=edit&amp;commit=<?= $commit_id ?>">
<p>
<textarea name="content" rows="10" cols="80"><?= Markup::escape($content) ?></textarea>
Summary of changes:
<input type="text" name="summary" class="summary" />
<input type="submit" value="Save changes" class="submit" />
</p>
</form>
<?php include('footer.php'); ?>
