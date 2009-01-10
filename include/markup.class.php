<?php

require_once('e_markup_xhtml.class.php');

class Markup
{
    public static function escape($in)
    {
        return htmlspecialchars($in, ENT_NOQUOTES, 'UTF-8');
    }

    public static function parseLinkTarget($ref)
    {
        $ref = strtr($ref, "\n", ' ');
        $parts = explode(':', $ref, 2);
        $valid = -1;
        if (count($parts) == 1)
        {
            $page = new WikiPage(explode('/', $ref));
            $valid = $page->isValid();
            $url = $page->getURL();
        }
        else if ($parts[0] == 'wp')
        {
            $url = 'http://en.wikipedia.org/wiki/'.strtr(implode('/', array_map('urlencode', explode('/', $parts[1]))), '+', '_');
            $ref = $parts[1].' (Wikipedia)';
        }
        else
            $url = $ref;
        return array($url, $ref, $valid);
    }

    public static function format($in)
    {
        $m = new eMarkupXHTML($in);
        return $m->format();
    }
}

?>
