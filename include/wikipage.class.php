<?php

class InvalidPageError extends Exception {}
class InvalidTreeError extends InvalidPageError {}
class PageNotFoundError extends InvalidPageError {}

class WikiPage
{
    public $path;
    public $object;

    const TYPE_PAGE = 0;
    const TYPE_IMAGE = 1;
    const TYPE_BINARY = 2;
    const TYPE_TREE = 3;

    public function __construct($path, $commit=NULL)
    {
        global $repo;
        $this->path = array();
        foreach ($path as $part)
            if ($part != '')
                array_push($this->path, $part);
        if ($commit === NULL)
            $commit = $repo->getObject($repo->getHead(Config::GIT_BRANCH));
        $this->commit = $commit;
        try
        {
            $this->object = WikiPage::find_page($commit, $path);
        }
        catch (InvalidPageError $e)
        {
            $this->object = NULL;
        }
    }

    public function get_url()
    {
        $url = Config::PATH;
        foreach ($this->path as $part)
            $url .= '/' . strtr(str_replace('_', '%5F', urlencode($part)), '+', '_');
        if ($this->object instanceof GitTree)
            $url .= '/';
        return $url;
    }

    public function get_name()
    {
        return implode('/', $this->path).($this->object instanceof GitTree ? '/' : '');
    }

    public function format()
    {
        return Markup::markup2html($this->object->data);
    }

    public function list_entries()
    {
        $entries = array();
        foreach ($this->object->nodes as $node)
        {
            array_push($entries, new WikiPage(array_merge($this->path, array($node->name)), $this->commit));
        }
        return $entries;
    }

    public function get_page_type()
    {
        if ($this->object instanceof GitTree)
            return self::TYPE_TREE;
        $mime_type = $this->get_mime_type();
        if ($mime_type == 'text/plain')
            return self::TYPE_PAGE;
        else if (!strncmp($mime_type, 'image/', 6))
            return self::TYPE_IMAGE;
        else
            return self::TYPE_BINARY;
    }

    public function get_mime_type()
    {
        if (!$this->object)
            return NULL;
        $mime = new MIME;
        return $mime->buffer_get_type($this->object->data, $this->get_name());
    }

    static public function from_url($name, $commit=NULL)
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
        return new WikiPage($path, $commit);
    }

    static public function find_page($commit, $path)
    {
        $cur = $commit->repo->getObject($commit->tree);
        for (; count($path); array_shift($path))
        {
            if ($cur->getType() != Git::OBJ_TREE)
                throw new InvalidTreeError;
            if ($path[0] == '')
                continue;
            if (!isset($cur->nodes[$path[0]]))
                throw new PageNotFoundError;
            $cur = $commit->repo->getObject($cur->nodes[$path[0]]->object);
        }
        return $cur;
    }
}

