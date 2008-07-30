<? $title = 'Edit profile'; ?>
<? include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($title); ?></h1>
<? if ($invalid_password): ?>
<div class="error">
Please give a sensible password (at least 3 characters).
</div>
<? endif; ?>
<form accept-charset="UTF-8" method="post" action="<?= Config::PATH ?>/:profile">
<p>
    E-mail address:
    <input type="text" name="email" class="text" value="<?= htmlspecialchars($user->email, 0, 'UTF-8'); ?>" />
</p>
<p>
    New password:
    <input type="password" name="newpass" class="text" />
</p>
<p>
    <input type="submit" value="Save changes" class="submit" />
</p>
</form>
<? include('footer.php'); ?>
