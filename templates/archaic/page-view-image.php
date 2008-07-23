<?php include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($page->get_name()) ?></h1>
<a href="<?= $page->get_url() ?>?action=get&amp;commit=<?= $commit_id ?>"><img src="<?= $page->get_url() ?>?action=image&amp;width=<?= Config::IMAGE_WIDTH ?>&amp;height=<?= Config::IMAGE_HEIGHT ?>" alt="<?= Markup::escape($page->get_name()) ?>" /></a>
<p><a href="<?= $page->get_url() ?>?action=get&amp;commit=<?= $commit_id ?>">View full size: “<?= Markup::escape($page->get_name()) ?>”</a></p>
<?php include('footer.php');
