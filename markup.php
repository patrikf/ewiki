<?php

function parse_link_target($ref)
{
    $ref = strtr($ref, "\n", ' ');
    $parts = explode(':', $ref, 2);
    if ($parts[0] == 'wp')
	$url = 'http://en.wikipedia.org/wiki/'.implode('/', array_map('urlencode', explode('/', (strtr($parts[1], ' ', '_')))));
    else if ($parts[0] == 'http')
	$url = $ref;
    else
	$url = '/'.implode('/', array_map('urlencode', explode('/', (strtr($ref, ' ', '_')))));
    return array($url, $ref);
}

function markup_escape($in, $raw=FALSE)
{
    return ($raw ? $in : htmlspecialchars($in, ENT_NOQUOTES, 'UTF-8'));
}

function markup_parse(&$in, &$out, $context)
{
    $raw = ($context == 'link_target');
    $newlines = 0;
    while (strlen($in))
    {
	if ($context == NULL && substr($in, 0, 1) == '#')
	{
	    $pos = strpos($in, "\n");
	    $in = ($pos === FALSE ? '' : substr($in, $pos+1));
	}
	else if (substr($in, 0, 1) == '~')
	{
	    $out .= substr($in, 1, 1);
	    $in = substr($in, 2);
	}
	else if ($context == 'cell' && (substr($in, 0, 1) == '|' || substr($in, 0, 1) == "\n"))
	    return;
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
	    markup_parse($m[2], $out, 'header');
	    $out .= sprintf('</h%d>', strlen($m[1]));
	    $in = substr($in, strlen($m[0]));
	}
	else if ($context == NULL && substr($in, 0, 1) == '|')
	{
	    $out .= '<table>';
	    markup_parse($in, $out, 'table');
	    $out .= '</table>';
	}
	else if ($context == NULL)
	{
	    $out .= '<p>';
	    markup_parse($in, $out, 'par');
	    $out .= '</p>';
	}
	else if ($context == 'par' && $newlines >= 2)
	    return;
	else if (!$raw && substr($in, 0, 2) == '[[')
	{
	    $in = substr($in, 2);
	    $target = '';
	    markup_parse($in, $target, 'link_target');
	    list($url, $caption) = parse_link_target($target);
	    $out .= sprintf('<a href="%s">', htmlspecialchars($url, 0, 'UTF-8'));
	    if (substr($in, 0, 1) == '|')
	    {
		$in = substr($in, 1);
		markup_parse($in, $out, 'link_title');
	    }
	    else
		$out .= markup_escape($caption);
	    $out .= '</a>';
	    $in = substr($in, 2);
	}
	else if ($context == 'link_target' && substr($in, 0, 1) == '|')
	    return;
	else if (($context == 'link_target' || $context == 'link_title') && substr($in, 0, 2) == ']]')
	    return;
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
		    markup_parse($in, $out, 'cell');
		    $out .= $header ? '</th>' : '</td>';
		}
		$out .= '</tr>';
	    }
	    else
		return;
	}
	else if ($context == 'strong' && substr($in, 0, 3) == "'''")
	{
	    $in = substr($in, 3);
	    return;
	}
	else if ($context == 'emph' && substr($in, 0, 2) == "''")
	{
	    $in = substr($in, 2);
	    return;
	}
	else if (!$raw && substr($in, 0, 3) == "'''")
	{
	    $in = substr($in, 3);
	    $out .= '<strong>';
	    markup_parse($in, $out, 'strong');
	    $out .= '</strong>';
	}
	else if (!$raw && substr($in, 0, 2) == "''")
	{
	    $in = substr($in, 2);
	    $out .= '<em>';
	    markup_parse($in, $out, 'emph');
	    $out .= '</em>';
	}
	else if (!$raw && substr($in, 0, 2) == '\\\\')
	{
	    $in = substr($in, 2);
	    $out .= '<br />';
	}
	else
	{
	    $pos = strcspn($in, "~#[|]'\\\n", 1)+1;
	    if ($pos === FALSE)
	    {
		$out .= $in;
		$in = '';
		break;
	    }
	    else
	    {
		$out .= markup_escape(substr($in, 0, $pos), $raw);
		$in = substr($in, $pos);
	    }
	}
	$newlines = 0;
    }
}

function markup_to_html($in)
{
    $out = '';
    markup_parse(&$in, &$out, NULL);
    return $out;
}

?>
