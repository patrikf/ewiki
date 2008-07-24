<?php include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->getName()) ?></h1>
<a href="<?= $page->getURL() ?>?action=get&amp;commit=<?= $commit_id ?>"><img src="<?= $page->getURL() ?>?action=image&amp;width=<?= Config::IMAGE_WIDTH ?>&amp;height=<?= Config::IMAGE_HEIGHT ?>" alt="<?= Markup::escape($page->getName()) ?>" /></a>
<p><a href="<?= $page->getURL() ?>?action=get&amp;commit=<?= $commit_id ?>">View full size: “<?= Markup::escape($page->getName()) ?>”</a></p>
<?php include('footer.php');
