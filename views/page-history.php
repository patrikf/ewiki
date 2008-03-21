<?php echo '<?xml version="1.0"?>'; ?>
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
    <a href="<?php echo $page->get_url(); ?>?action=edit">edit</a>
    <a href="<?php echo $page->get_url(); ?>?action=history" class="active">history</a>
</div>
<h1 id="pagetitle"><?php echo markup_escape($page->get_name()); ?></h1>
<table class="history">
<?php
    foreach ($history as $entry)
	printf('<tr><td><a href="%s?commit=%s">%s</a></td><td>%s</td><td>%s</td></tr>',
	    $page->get_url(),
	    $entry->commit,
	    markup_escape(strftime('%Y-%m-%d %H:%M', $entry->time)),
	    markup_escape($entry->author),
	    markup_escape($entry->summary));
?>
</table>
</div>
</body>
</html>
