<?php

class Markup
{
    private static function parseLinkTarget($ref)
    {
        $ref = strtr($ref, "\n", ' ');
        $parts = explode(':', $ref, 2);
        $valid = -1;
        if (count($parts) == 1)
        {
            $page = new WikiPage(explode('/', $ref));
            $valid = ($page->object !== NULL);
            $url = $page->getURL();
        }
        else if ($parts[0] == 'wp')
        {
            $url = 'http://en.wikipedia.org/wiki/'.strtr(implode('/', array_map('urlencode', explode('/', $parts[1]))), '+', '_');
        }
        else
            $url = $ref;
        return array($url, $ref, $valid);
    }

    public static function escape($in, $raw=FALSE)
    {
        return ($raw ? $in : htmlspecialchars($in, ENT_NOQUOTES, 'UTF-8'));
    }

    private static function parseSpecial($special)
    {
        $special = array_map('trim', explode(' ', $special, 2));
        if ($special[0] == 'image')
        {
            $view = new View('markup-image.php');
            $args = explode(':', $special[1]);
            $name = array_pop($args);
            $width = intval(array_shift($args));
            $height = intval(array_shift($args));
            if (!$width && !$height)
            {
                $width = Config::IMAGE_WIDTH;
                $height = Config::IMAGE_HEIGHT;
            }
            try
            {
                $page = new WikiPage($name);
            }
            catch (Exception $e)
            {
                $page = NULL;
            }
            $view->page = $page;
            if ($page)
                $view->page_type = $page->getPageType();
            else
                $view->page_type = NULL;
            $view->width = $width;
            $view->height = $height;
            return $view->display(TRUE);
        }
        else
            return '';
    }

    private static function parse(&$in, &$out, $context = null)
    {
        $raw = ($context == 'link_target');
        $newlines = 0;
        while (strlen($in))
        {
            if ($context == NULL && $in{0} == '#')
            {
                $c = substr($in, 1, 1);
                $is_comment = ($c == '' || ctype_space($c));
                if (!$is_comment && $context)
                    return 1;
                $pos = strpos($in, "\n");
                if ($pos === FALSE)
                    $pos = strlen($in);
                else
                    $pos++;
                if (!$is_comment)
                    $out .= self::parseSpecial(substr($in, 1, $pos));
                $in = substr($in, $pos);
            }
            else if (substr($in, 0, 1) == '~')
            {
                $out .= substr($in, 1, 1);
                $in = substr($in, 2);
            }
            else if ($context == 'cell' && (substr($in, 0, 1) == '|' || substr($in, 0, 1) == "\n"))
                return 0;
            else if ($context == 'list_element' && $newlines >= 2)
                return 1;
            else if ($context == 'list_element' && $newlines && $in{0} == '*')
                return 0;
            else if (substr($in, 0, 1) == "\n")
            {
                $newlines++;
                $out .= "\n";
                $in = substr($in, 1);
                continue;
            }
            else if ($context == NULL && preg_match('/^(={2,4})\s*(.+?)\s*=*\s*(?=\n|$)/', $in, $m))
            {
                $out .= sprintf('<h%d>', strlen($m[1]));
                Markup::parse($m[2], $out, 'header');
                $out .= sprintf('</h%d>', strlen($m[1]));
                $in = substr($in, strlen($m[0]));
            }
            else if ($context == NULL && substr($in, 0, 1) == '|')
            {
                $out .= '<table>';
                Markup::parse($in, $out, 'table');
                $out .= '</table>';
            }
            else if ($context == NULL && substr($in, 0, 1) == '*')
            {
                $out .= '<ul>';
                Markup::parse($in, $out, 'list');
                $out .= '</ul>';
            }
            else if ($context == NULL)
            {
                $out .= '<p>';
                Markup::parse($in, $out, 'par');
                $out .= '</p>';
            }
            else if ($context == 'par' && $newlines >= 2)
                return 0;
            else if (!$raw && substr($in, 0, 2) == '[[')
            {
                $in = substr($in, 2);
                $target = '';
                Markup::parse($in, $target, 'link_target');
                list($url, $caption, $valid) = Markup::parseLinkTarget($target);
                $out .= '<a href="' . htmlspecialchars($url, 0, 'UTF-8') . '"';
                if (!$valid)
                    $out .= ' class="new"';
                $out .= '>';
                if (substr($in, 0, 1) == '|')
                {
                    $in = substr($in, 1);
                    Markup::parse($in, $out, 'link_title');
                }
                else
                    $out .= Markup::escape($caption);
                $out .= '</a>';
                $in = substr($in, 2);
            }
            else if ($context == 'link_target' && substr($in, 0, 1) == '|')
                return 0;
            else if (($context == 'link_target' || $context == 'link_title') && substr($in, 0, 2) == ']]')
                return 0;
            else if ($context == 'table')
            {
                if (substr($in, 0, 1) == '|')
                {
                    $out .= '<tr>';
                    while (substr($in, 0, 1) == '|')
                    {
                        $header = (substr($in, 1, 1) == '=');
                        $in = substr($in, $header ? 2 : 1);
                        $out .= $header ? '<th>' : '<td>';
                        Markup::parse($in, $out, 'cell');
                        $out .= $header ? '</th>' : '</td>';
                    }
                    $out .= '</tr>';
                }
                else
                    return 0;
            }
            else if ($context == 'list')
            {
                $r = 0;
                while (substr($in, 0, 1) == '*' && !$r)
                {
                    $in = substr($in, 1);
                    $out .= '<li>';
                    $r = Markup::parse($in, $out, 'list_element');
                    $out .= '</li>';
                }
                return 0;
            }
            else if ($context == 'strong' && substr($in, 0, 3) == "'''")
            {
                $in = substr($in, 3);
                return 0;
            }
            else if ($context == 'emph' && substr($in, 0, 2) == "''")
            {
                $in = substr($in, 2);
                return 0;
            }
            else if (!$raw && substr($in, 0, 3) == "'''")
            {
                $in = substr($in, 3);
                $out .= '<strong>';
                Markup::parse($in, $out, 'strong');
                $out .= '</strong>';
            }
            else if (!$raw && substr($in, 0, 2) == "''")
            {
                $in = substr($in, 2);
                $out .= '<em>';
                Markup::parse($in, $out, 'emph');
                $out .= '</em>';
            }
            else if (!$raw && substr($in, 0, 2) == '\\\\')
            {
                $in = substr($in, 2);
                $out .= '<br />';
            }
            else
            {
                $pos = strcspn($in, "~#[|]*'\\\n", 1)+1;
                if ($pos === FALSE)
                {
                    $out .= $in;
                    $in = '';
                    break;
                }
                else
                {
                    $out .= Markup::escape(substr($in, 0, $pos), $raw);
                    $in = substr($in, $pos);
                }
            }
            $newlines = 0;
        }
        return 0;
    }

    public static function format($in)
    {
        $out = '';
        Markup::parse(&$in, &$out, NULL);
        return $out;
    }
}

