<? $title = 'Recent changes'; ?>
<? $recent_changes_feeds = TRUE; ?>
<? include('header.php'); ?>
<h1 id="pagetitle">Recent changes</h1>
<div class="commits">
<? foreach ($commits as $commit): ?>
    <div class="commit" id="commit-<?= $commit->commit_id ?>">
        <div style="float: right">
            <div class="sha1"><?= $commit->commit_id ?></div>
            <div class="time"><?= Markup::escape(strftime('%Y-%m-%d %H:%M', $commit->time)) ?></div>
            <div class="author"><?= Markup::escape($commit->author) ?></div>
        </div>
        <div class="summary"><?= Markup::escape($commit->summary) ?></div>
        <? if ($commit->detail): ?>
            <div class="detail"><?= Markup::escape($commit->detail) ?></div>
        <? endif; ?>
        <? if ($commit->changes || count($commit->merge)): ?>
            <table class="changes">
            <? foreach ($commit->merge as $merge): ?>
                <tr class="change">
                    <td class="type">merged</td>
                    <td class="subject"><?= Markup::escape($merge) ?></td>
                </tr>
            <? endforeach; ?>
            <? foreach ($commit->changes as $change): ?>
                <tr class="change">
                    <td class="type"><?= $change->type ?></td>
                    <td class="subject">
                        <a href="<?= Markup::escape($change->subject_url) ?>"><?= Markup::escape($change->subject) ?></a>
                    </td>
                </tr>
            <? endforeach; ?>
            </table>
        <? endif; ?>
        <div style="clear: both"></div>
    </div>
<? endforeach; ?>
</div>
<? include('footer.php');
