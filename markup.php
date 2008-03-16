<?php

function htmlescape($in)
{
    return htmlspecialchars($in, ENT_NOQUOTES, 'UTF-8');
}

function markup_to_html($in)
{
    $html = '';
    $front = TRUE; $i = 0;
    $cur = '';
    do
    {
	$cur .= htmlescape(substr($in, 0, $i));
	$in = substr($in, $i);
	if (substr($in, 0, 1) == "\n")
	{
	    $front = TRUE;
	    $in = substr($in, 1);
	}
	if ($front && substr($in, 0, 1) == "#")
	{
	    $pos = strpos($in, "\n", 1);
	    $in = ($pos !== FALSE ? substr($in, $pos) : '');
	}
	else if ($front && substr($in, 0, 1) == "\n")
	{
	    if ($cur != '')
		$html .= '<p>'.$cur.'</p>';
	    $cur = '';
	}
	else if ($front && preg_match('/^(={2,4})\s*(.+?)\s*=*(?=\n|$)/', $in, $m))
	{
	    if ($cur != '')
		$html .= '<p>'.$cur.'</p>';
	    $cur = '';
	    $html .= sprintf('<h%d>%s</h%1$d>', strlen($m[1]), markup_to_html($m[2]));
	    $in = substr($in, strlen($m[0]));
	}
	else if ($in{0} == '~')
	{
	    $cur .= htmlescape($in{1});
	    $in = substr($in, 2);
	}
	else if (preg_match('/^\'{3}(.*?[^~])\'{3}/s', $in, $m))
	{
	    $cur .= '<strong>'.markup_to_html($m[1]).'</strong>';
	    $in = substr($in, strlen($m[0]));
	}
	else if (preg_match('/^\'{2}(.*?[^~])\'{2}/s', $in, $m))
	{
	    $cur .= '<em>'.markup_to_html($m[1]).'</em>';
	    $in = substr($in, strlen($m[0]));
	}
	else if (preg_match('/^\[\[(.+?)(?:\|(.*?[^~]))?\]\]/s', $in, $m))
	{
	    $caption = htmlescape($m[1]);
	    $ref = strtr($m[1], "\n", ' ');
	    $parts = explode(':', $ref, 2);
	    if ($parts[0] == 'wp')
		$url = 'http://en.wikipedia.org/wiki/'.implode('/', array_map('urlencode', explode('/', (strtr($parts[1], ' ', '_')))));
	    else if ($parts[0] == 'http')
		$url = $m[1];
	    else
		$url = 'http://fimml.at.local/ewiki/'.implode('/', array_map('urlencode', explode('/', (strtr($ref, ' ', '_')))));
	    if (!empty($m[2]))
		$caption = markup_to_html($m[2]);
	    $cur .= sprintf('<a href="%s">%s</a>', htmlspecialchars($url, 0, 'UTF-8'), $caption);
	    $in = substr($in, strlen($m[0]));
	}
	else
	{
	    $cur .= htmlescape($in{0});
	    $in = substr($in, 1);
	}
	$front = FALSE;
    }
    while (($i = strcspn($in, "~'[\n")) != strlen($in));

    $cur .= htmlescape($in);
    if ($cur != '')
	$html .= '<p>'.$cur.'</p>';
    $cur = '';
    return $html;
}

/*function markup_to_html($in)
{
    /* process line-wise to get global structure *//*
    $lines = array_map('trim', explode("\n", $in));
    $pars = array();
    $cur = '';
    $line = array_shift($lines);
    while ($line !== NULL)
    {
	if (substr($line, 0, 1) == '#')
	{
	}
	else if (substr($line, 0, 1) == '|')
	{
	    if ($cur != '')
		array_push($pars, '<p>'.markup_to_html2($cur).'</p>');
	    $cur = '';
	    $table = '<table>';
	    while ($line !== NULL && substr($line, 0, 1) == '|')
	    {
		$table .= '<tr>';
		while ($line != '|' && $line != '')
		{
		    assert(preg_match('/^\|(=)?(.*?)(?:([^~])(?=\|)|$)/', $line, $m));
		    $table .= sprintf('<%s>%s</%1$s>', $m[1] == '=' ? 'th' : 'td', markup_to_html2(trim($m[2].$m[3])));
		    $line = substr($line, strlen($m[0]));
		}

		$table .= '</tr>';
		$line = array_shift($lines);
	    }
	    array_push($pars, $table);
	    continue;
	}
	else if (preg_match('/^(={2,4})\s*(.+?)\s*=*$/', $line, $m))
	{
	    if ($cur != '')
		array_push($pars, '<p>'.markup_to_html2($cur).'</p>');
	    $cur = '';
	    array_push($pars, sprintf('<h%d>%s</h%1$d>', strlen($m[1]), markup_to_html2($m[2])));
	}
	else if ($line == '')
	{
	    if ($cur != '')
		array_push($pars, '<p>'.markup_to_html2($cur).'</p>');
	    $cur = '';
	}
	else
	    $cur .= $line."\n";
	$line = array_shift($lines);
    }
    return join('', $pars);
}*/

?>
