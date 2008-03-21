<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>eWiki: <?php echo markup_escape($page->get_name()); ?></title>
<link rel="stylesheet" href="/style.css" />
</head>
<body>
<div id="head">
</div>
<div id="page">
<div id="pagelinks">
    <a href="<?php echo $page->get_url(); ?>">view</a>
    <a href="<?php echo $page->get_url(); ?>?action=edit" class="active">edit</a>
    <a href="<?php echo $page->get_url(); ?>?action=history">history</a>
</div>
<h1 id="pagetitle"><?php echo markup_escape($page->get_name()); ?></h1>
<form id="edit-form" method="post" action="<?php echo $page->get_url(); ?>?action=edit&commit=<?php echo $commit; ?>">
<textarea name="content"><?php echo markup_escape($content); ?></textarea>
<p>Summary of changes:
<input type="text" name="summary" class="summary" /></p>
<input type="submit" value="Änderungen speichern" class="submit" />
</form>
</div>
</body>
</html>
