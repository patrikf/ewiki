<? if ($page_type == WikiPage::TYPE_IMAGE): ?>
<div class="image">
    <a href="<?= $page->getURL() ?>">
        <img
            src="<?= $page->getURL() ?>?action=image&amp;width=<?= $width ?>&amp;height=<?= $height ?>"
            alt="<?= Markup::escape($page->getName()) ?>" />
    </a>
</div>
<? elseif (!$page_type): ?>
<div class="error">
    No such file: <?= $page->link() ?>
</div>
<? else: ?>
<div class="error">
    Not an image: <?= $page->link() ?>
</div>
<? endif; ?>
