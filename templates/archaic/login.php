<? $title = 'Login'; ?>
<? include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($title); ?></h1>
<? if ($wrong): ?>
<div class="error">
Sorry, wrong username or password.
</div>
<? endif; ?>
<form accept-charset="UTF-8" method="post" action="<?= Config::PATH ?>/:login<?= htmlspecialchars($goto, 0, 'UTF-8') ?>">
<p>
    Username:
    <input type="text" name="user" class="text" />
</p>
<p>
    Password:
    <input type="password" name="password" class="text" />
</p>
<div class="par submit">
<input type="submit" value="Login" class="submit" />
</div>
</form>
<? include('footer.php'); ?>
