<?php


class WikiPage
{
    public $path;
    public $object;
    protected $commit;
    protected $mime_type = NULL;

    /* don't use 0 (<-> NULL) */
    const TYPE_PAGE = 1;
    const TYPE_IMAGE = 2;
    const TYPE_BINARY = 3;
    const TYPE_TREE = 4;

    public function __construct($path, $commit=NULL)
    {
        global $repo;
        $this->path = array();
        if (!is_array($path))
            $path = explode('/', $path);
        foreach ($path as $part)
            if ($part != '')
                array_push($this->path, $part);
        if ($commit === NULL)
            $commit = $repo->getObject($repo->getHead(Config::GIT_BRANCH));
        $this->commit = $commit;
        $name = $commit->find($path);
        $this->object = $name ? $commit->repo->getObject($name) : NULL;
    }

    public function getURL()
    {
        $url = Config::PATH;
        foreach ($this->path as $part)
            $url .= '/' . strtr(str_replace('_', '%5F', urlencode($part)), '+', '_');
        if ($this->object instanceof GitTree)
            $url .= '/';
        return $url;
    }

    public function getName()
    {
        return implode('/', $this->path).($this->object instanceof GitTree ? '/' : '');
    }

    public function link()
    {
        return sprintf('<a href="%s"%s>%s</a>', $this->getURL(), $this->isValid() ? '' : ' class="new"', Markup::escape($this->getName()));
    }

    public function format()
    {
        return Markup::format($this->object->data);
    }

    public function listEntries()
    {
        $entries = array();
        foreach ($this->object->nodes as $node)
        {
            array_push($entries, new WikiPage(array_merge($this->path, array($node->name)), $this->commit));
        }
        return $entries;
    }

    public function getPageType()
    {
        if ($this->object instanceof GitTree)
            return self::TYPE_TREE;
        $mime_type = $this->getMimeType();
        if ($mime_type === NULL)
            return NULL;
        else if ($mime_type == 'text/plain')
            return self::TYPE_PAGE;
        else if (!strncmp($mime_type, 'image/', 6))
            return self::TYPE_IMAGE;
        else
            return self::TYPE_BINARY;
    }

    public function getMimeType()
    {
        if (!$this->object)
            return NULL;
        if (!$this->mime_type)
        {
            $mime = new MIME;
            $this->mime_type = $mime->bufferGetType($this->object->data, $this->getName());
        }
        return $this->mime_type;
    }

    static public function fromURL($name, $commit=NULL)
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

    public function getPageHistory()
    {
        if (!$this->object)
            return array();
        $commits = $this->commit->getHistory();
        $r = array();
        foreach ($commits as $commit)
        {
            $common = FALSE;
            foreach ($commit->parents as $parent)
            {
                $parent = $commit->repo->getObject($parent);
                $blob = $parent->find($this->path);
                if ($common === FALSE)
                    $common = $blob;
                else if ($blob !== $common)
                {
                    $common = TRUE;
                    break;
                }
            }
            if ($common === FALSE)
                $common = NULL;

            $blob = $commit->find($this->path);
            if ($common !== $blob)
                array_push($r, $commit);
        }
        return $r;
    }

    public function getLastModified()
    {
        if (!$this->object)
            return NULL;
        $commits = $this->commit->getHistory();
        $commits = array_reverse($commits);
        $r = NULL;
        $lastblob = $this->object->getName();
        foreach ($commits as $commit)
        {
            $blobname = $commit->find($this->path);
            if ($blobname != $lastblob)
                break;
            $r = $commit->committer->time;
        }
        assert($r !== NULL); /* something is seriously wrong if this happens */
        return $r;
    }

    public function isValid()
    {
        return ($this->object && (!Config::IGNORE_EMPTY_PAGES || !empty($this->object->data)));
    }
}

