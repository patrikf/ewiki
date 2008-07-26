<? $title = $page->getName(); ?>
<? include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->getName()); ?></h1>
<? if(Config::ALLOW_EDIT): ?>
<form id="edit-form" enctype="multipart/form-data" accept-charset="UTF-8" method="post" action="<?= $page->getURL() ?>?action=edit&amp;commit=<?= $commit_id ?>">
<div class="par">
    <input type="radio" name="type" value="file" class="form-opt" id="file-opt"<?= $is_binary ? ' checked="checked"' : '' ?> /><!--
    --><label for="file-opt">
    <? if (!$page_type): ?>
    Upload a file
    <? else: ?>
    Upload a file, replacing existing content
    <? endif; ?>
    </label>

    <div id="file-opt-target">
        <input type="file" name="file" class="file" id="file-input" />
    </div>
</div>

<div class="par">
    <input type="radio" name="type" value="page" class="form-opt" id="page-opt"<?= !$is_binary ? ' checked="checked"' : '' ?> /><label for="page-opt">
    <? if (!$page_type): ?>
    Create a new wiki page
    <? elseif ($page_type == WikiPage::TYPE_PAGE): ?>
    Edit page
    <? else: ?>
    Create a new wiki page, replacing existing content
    <? endif; ?>
    </label>

    <div id="page-opt-target">
        <textarea name="content" rows="10" cols="80"><?= Markup::escape($content) ?></textarea>
    </div>
</div>

<div class="par">
    Summary of changes:
    <input type="text" name="summary" class="summary" />
    <div class="submit">
        <input type="submit" value="Save changes" class="submit" />
    </div>
</div>
</form>
<? else: ?>
<div class="par">
<? if (!$page_type): ?>
This page does not exist yet.
<? elseif ($page_type == WikiPage::TYPE_PAGE): ?>
View page source
<div id="page-opt-target">
<textarea name="content" rows="10" cols="80"><?= Markup::escape($content) ?></textarea>
</div>
<? else: ?>
Sorry, no page source available.
<? endif; ?>
</div>
<? endif; ?>
<? include('footer.php'); ?>
