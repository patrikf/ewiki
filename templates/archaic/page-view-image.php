<p>
    <a href="<?= $page->getURL() ?>?action=get&amp;commit=<?= $commit_id ?>">
        <img
            src="<?= $page->getURL() ?>?action=image&amp;commit=<?= $commit_id ?>&amp;width=<?= Config::IMAGE_WIDTH ?>
            &amp;height=<?= Config::IMAGE_HEIGHT ?>" alt="<?= Markup::escape($page->getName()) ?>" />
    </a>
</p>
<p>
    <a href="<?= $page->getURL() ?>?action=get&amp;commit=<?= $commit_id ?>">
        View full size: “<?= Markup::escape($page->getName()) ?>”
    </a>
</p>
