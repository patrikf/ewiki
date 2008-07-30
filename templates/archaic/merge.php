<? $title = sprintf('Merge “%s” in “%s”', $B->branch, $page_name); ?>
<? include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($title) ?></h1>
<h2>What happened?</h2>
<p>
A conflict occured while editing <?= $A->page->link(); ?>.

This means that two people
<? if ($fresh): ?> (you and someone else) <? endif; ?>
have been working on the page at the same time.

<? if ($fresh): ?><strong>Although your changes are not visible on the page right
now, your work has been saved and is not lost.</strong><? endif; ?>
</p>
<h2>How can I resolve the conflict?</h2>
<? if (0): ?>
<p>
You can <em>merge</em> (combine) both changes here. This is usually rather
simple: you look at the most recent version (shown on the left) and the text
<? if ($fresh): ?>
you just submitted
<? else: ?>
that was submitted later on
<? endif; ?>
(right). Then you try to combine the changes: if you see a
new text passage on the left, you also add it on the right, and so on. As soon
as you are finished, click the “Save” button and <strong>the right version will become
the new
“<?= Markup::escape($page_name) ?>” page.</strong>
</p>
<p><em>
Please do NOT simply edit the page again, use this page instead.
</em></p>
<h2>This is too complicated / too much work / ...</h2>
<p>
If you do not want to perform the merge yourself—because you do not understand
what you should do here, because the page is rather long and it would be much
work, ...—you can simply leave this page and go on with what you wanted to do
next. An administrator will resolve the conflict later on.
</p>
<? if ($fresh): ?>
<p>
Again, your changes have been saved and will become visible as soon as this
conflict has been resolved.
</p>
<? endif; ?>
<? else: ?>
<p>
In the future, it will be possible to directly resolve conflicts here. For
now, just go on with your work. An administrator will resolve the issue.
</p>
<p><em>
Do not make the same changes to the page again, but wait for an
administrator to resolve it. Of course, you are still encouraged to add other
information to the page.
</em></p>
<? endif; ?>
<? include('footer.php'); ?>
