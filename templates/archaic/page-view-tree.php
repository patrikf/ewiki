<? foreach ($entries as $entry): ?>
    <p>
        <a href="<?= $entry->url ?>">
            <?= Markup::escape($entry->name) ?>
        </a>
    </p>
<? endforeach; ?>
