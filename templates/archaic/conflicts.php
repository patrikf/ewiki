<? $title = 'Unmerged conflicts'; ?>
<? include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($title) ?></h1>
<p>
<? if (count($conflicts)): ?>
    <ul>
    <? foreach ($conflicts as $conflict): ?>
        <li><a href="<?= $conflict->merge_url ?>"><?= $conflict->branch ?></a></li>
    <? endforeach; ?>
    </ul>
<? else: ?>
    There are no unmerged conflicts.
<? endif; ?>
</p>
<? include('footer.php'); ?>
