<? $title = 'Recent changes'; ?>
<? $recent_changes_feeds = TRUE; ?>
<? include('header.php'); ?>
<h1 id="pagetitle">Recent changes</h1>
<div class="commits">
<? foreach ($commits as $commit): ?>
    <div class="commit">
        <a name="<?= $commit->commit_id ?>"></a>
        <div style="float: right">
            <div class="sha1"><?= $commit->commit_id ?></div>
            <div class="time"><?= Markup::escape(strftime('%Y-%m-%d %H:%M', $commit->time)) ?></div>
            <div class="author"><?= $commit->author ?></div>
        </div>
        <div class="summary"><?= $commit->summary ?></div>
        <? if ($commit->detail): ?>
            <div class="detail"><?= $commit->detail ?></div>
        <? endif; ?>
        <? if ($commit->changes || count($commit->merge)): ?>
            <table class="changes">
            <? foreach ($commit->merge as $merge): ?>
                <tr class="change"><td class="type">merged</td><td class="subject"><?= $merge ?></td></tr>
            <? endforeach; ?>
            <? foreach ($commit->changes as $change): ?>
                <tr class="change"><td class="type"><?= $change->type ?></td><td class="subject"><a href="<?= $change->subject_url ?>"><?= $change->subject ?></a></td></tr>
            <? endforeach; ?>
            </table>
        <? endif; ?>
        <div style="clear: both"></div>
    </div>
<? endforeach; ?>
</div>
<? include('footer.php');
