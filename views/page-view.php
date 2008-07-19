<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo Config::WIKI_NAME; ?>: <?php echo Markup::escape($page->get_name()); ?></title>
<link rel="stylesheet" href="/style.css" />
</head>
<body>
<div id="head">
</div>
<div id="page">
<div id="pagelinks">
    <a href="<?php echo $page->get_url(); ?>" class="active">view</a>
    <a href="<?php echo $page->get_url(); ?>?action=edit">edit</a>
    <a href="<?php echo $page->get_url(); ?>?action=history">history</a>
</div>
<h1 id="pagetitle"><?php echo Markup::escape($page->get_name()); ?></h1>
<?php echo $page->format(); ?>
</div>
</body>
</html>
