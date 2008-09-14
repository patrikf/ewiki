<ul>
<? foreach ($entries as $entry): ?>
    <li>
        <a href="<?= $entry->url ?>">
            <?= Markup::escape($entry->name) ?>
        </a>
    </li>
<? endforeach; ?>
</ul>
