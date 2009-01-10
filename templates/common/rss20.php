<?= '<?xml version="1.0"?>' ?>
<rss version="2.0">
    <channel>
        <title><?= Config::WIKI_NAME ?>: Recent changes</title>
        <link><?= 'http://' . $_SERVER['SERVER_NAME'] . Config::PATH ?></link>
        <description>Recent changes for <?= Config::WIKI_NAME ?></description>
        <language>en-us</language>
        <pubDate><?= date('r', $commits[0]->time) ?></pubDate>
        <lastBuildDate><?= date('r', $commits[0]->time) ?></lastBuildDate>
        <docs>http://blogs.law.harvard.edu/tech/rss</docs>
        <generator>eWiki</generator>
        <? foreach ($commits as $commit): ?>
        <item>
            <title><?= Markup::escape($commit->summary) ?></title>
            <link><?= 'http://' . $_SERVER['SERVER_NAME'] . Config::PATH . '/:recent/' . $maxentries . '#' . $commit->commit_id ?></link>
            <? if($commit->detail): ?>
                <description><?= Markup::escape($commit->detail) ?></description>
            <? endif; ?>
            <author><?= Markup::escape($commit->email) ?> (<?= Markup::escape($commit->author) ?>)</author>
            <guid><?= $commit->commit_id ?></guid>
        </item>
        <? endforeach; ?>
  </channel>
</rss>
