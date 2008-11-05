<ul>
<? foreach ($entries as $entry): ?>
    <li>
        <?= $entry->link() ?>
    </li>
<? endforeach; ?>
</ul>
