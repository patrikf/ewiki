<?php

class WikiPage
{
    function __construct($path)
    {
	global $repository;

	$this->path = $path;

	$head = $repository->getObject($repository->getHead('master'));
	$cur = $repository->getObject($head->tree);
	while (count($path))
	{
	    if ($cur->getType() != Git::OBJ_TREE)
		die('Not a tree');
	    if ($path[0] == '')
		break;
	    if (!isset($cur->nodes[$path[0]]))
		die('Not found');
	    $cur = $repository->getObject($cur->nodes[array_shift($path)]->object);
	}
	$this->object = $cur;
    }
    function get_url()
    {
	$url = '';
	foreach ($this->path as $part)
	    $url .= '/'.str_replace('%20', '_', urlencode($part));
	return $url;
    }
    function get_name()
    {
	return implode('/', $this->path);
    }
    function format()
    {
	return markup_to_html($this->object->data);
    }

    static function from_url($name)
    {
	$path = array();
	$dir = FALSE;
	foreach (explode('/', $name) as $part)
	{
	    $dir = FALSE;
	    if (!empty($part))
		array_push($path, urldecode(strtr($part, '_', ' ')));
	    else
		$dir = TRUE;
	}
	if (count($path) == 0)
	    $path = array('Home');
	else if ($dir)
	    array_push($path, '');
	return new WikiPage($path);
    }
}

?>
