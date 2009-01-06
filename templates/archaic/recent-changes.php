<? $title = 'Recent changes'; ?>
<? include('header.php'); ?>
<h1 id="pagetitle">Recent changes</h1>
<div class="commits">
<? foreach ($commits as $commit): ?>
    <div class="commit">
        <div class="author"><?= $commit->author ?></div>
        <div class="summary"><?= $commit->summary ?></div>
        <? if ($commit->detail): ?>
            <div class="detail"><?= $commit->detail ?></div>
        <? endif; ?>
        <? if ($commit->changes): ?>
            <div class="changes">
            <? foreach ($commit->changes as $change): ?>
                <div class="change"><?= $change->type ?> <a href="<?= $change->subject_url ?>"><?= $change->subject ?></a></div>
            <? endforeach; ?>
            </div>
        <? endif; ?>
    </div>
<? endforeach; ?>
</div>
<? include('footer.php');
