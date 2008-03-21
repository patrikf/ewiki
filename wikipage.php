<?php

class InvalidPageError extends Exception {}
class InvalidTreeError extends InvalidPageError {}
class PageNotFoundError extends InvalidPageError {}

class WikiPage
{
    function __construct($path)
    {
	global $repo;

	$this->path = $path;

	$head = $repo->getObject($repo->getHead('master'));
	try
	{
	    $this->object = $repo->getObject(WikiPage::find_page($head, $path));
	}
	catch (InvalidPageError $e)
	{
	    $this->object = NULL;
	}
    }
    function get_url()
    {
	$url = '';
	foreach ($this->path as $part)
	    $url .= '/'.strtr(urlencode($part), '+', '_');
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
    static function find_page($commit, $path)
    {
	$cur = $commit->tree;
	while (count($path))
	{
	    $cur = $commit->repo->getObject($cur);
	    if ($cur->getType() != Git::OBJ_TREE)
		throw new InvalidTreeError;
	    if ($path[0] == '')
		break;
	    if (!isset($cur->nodes[$path[0]]))
		throw new PageNotFoundError;
	    $cur = $cur->nodes[array_shift($path)]->object;
	}
	return $cur;
    }
}

?>
