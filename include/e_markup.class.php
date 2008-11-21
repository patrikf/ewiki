<?php

function array_flatten($a)
{
    $r = array();
    foreach ($a as $x)
    {
        if (is_array($x))
            $r = array_merge($r, array_flatten($x));
        else
            array_push($r, $x);
    }
    return $r;
}

abstract class eMarkup
{
    protected $content;
    protected $ctx;

    public function __construct($content=NULL)
    {
        $this->setContent($content);
    }

    public function setContent($content=NULL)
    {
        $this->content = $content;
    }

    /*
     * Parsing bits prototypes.
     * Note that fmt_plain() is the only function that gets unchecked input.
     * If necessary, it should convert successive whitespace into single
     * spaces and escape input.
     */
             protected function fmt_page($blocks) { return implode('', $blocks); }
    abstract protected function fmt_par($s);
    abstract protected function fmt_heading($level, $s);
    abstract protected function fmt_list($items);
    abstract protected function fmt_listitem($s);
             protected function fmt_sublist($items) { return $this->fmt_list($items); }
    abstract protected function fmt_table($rows);
    abstract protected function fmt_row($cells);
    abstract protected function fmt_cell($s);
             protected function fmt_cell_head($s) { return $this->fmt_cell($s); }
             protected function fmt_comment($s) { return ''; }
    abstract protected function fmt_emph($s);
    abstract protected function fmt_strong($s);
    /* Auto-generated vs. user-provided label */
             protected function fmt_link($url, $label) { return $this->fmt_labeled_link($url, $label); }
    abstract protected function fmt_labeled_link($url, $user_label);
             protected function fmt_plain($s) { return $s; }

    static protected function comment_filter($line)
    {
        return $line != '#' && (strlen($line) < 2 || $line{0} != '#' || !ctype_space($line{1}));
    }

    protected function mklink($target, $label=NULL)
    {
        if ($label === NULL)
            return $this->fmt_link($target, $target);
        else
            return $this->fmt_labeled_link($target, $label);
    }

    protected function parse_link($in, &$a=0)
    {
        $target = '';
        while ($a < strlen($in))
        {
            $b = $a+strcspn($in, '\\|]', $a);
            $target .= substr($in, $a, $b-$a);
            if ($b == strlen($in))
                break;

            if ($in{$b} == '\\')
            {
                $target .= $in{$b+1};
                $a = $b+2;
            }
            else if ($in{$b} == '|')
            {
                $a = $b+1;
                $label = $this->parse_par($in, $a, 'link_label');
                return $this->mklink($target, $label);
            }
            else if (substr($in, $b, 2) == ']]')
            {
                $a = $b+2;
                break;
            }
            else
            {
                $target .= $in{$b};
                $a = $b+1;
            }
        }
        return $this->mklink($target);
    }

    protected function parse_par($in, &$a=0, $ctx='par')
    {
        $r = '';
        while ($a < strlen($in))
        {
            if (!preg_match("/ ( ''' | '' | \[\[ | ]] | \\\ ) /x", $in, $m, PREG_OFFSET_CAPTURE, $a))
            {
                $r .= $this->fmt_plain(substr($in, $a));
                break;
            }
            list($match, $b) = $m[1];
            $r .= $this->fmt_plain(substr($in, $a, $b-$a));

            $a = $b+strlen($match);
            if ($match == '\\')
            {
                $r .= $this->fmt_plain($in{$a});
                $a++;
                continue;
            }
            else if ($match == "'''")
            {
                if ($ctx == 'strong')
                    return $r;
                $r .= $this->fmt_strong($this->parse_par($in, $a, 'strong'));
                continue;
            }
            else if ($match == "''")
            {
                if ($ctx == 'emph')
                    return $r;
                $r .= $this->fmt_emph($this->parse_par($in, $a, 'emph'));
                continue;
            }
            else if ($match == '[[')
            {
                if ($ctx == 'link_label')
                {
                    /* do not nest links -> reset */
                    $a = $b;
                    return $r;
                }
                $r .= $this->parse_link($in, $a);
                continue;
            }
            else if ($match == ']]')
            {
                if ($ctx == 'link_label')
                    return $r;
            }
            /* pass-through for control sequence */
            $r .= $this->fmt_plain($match);
            continue;
        }
        return $r;
    }

    protected function parse_list($in)
    {
        $in = explode("\n*", substr($in, 1));
        $items = array();
        for ($i = 0; $i < count($in);)
        {
            /* simple item */
            if ($in[$i]{0} != '*')
            {
                array_push($items, $this->fmt_listitem($this->parse_par($in[$i])));
                $i++;
                continue;
            }

            /* sublist */
            for ($j = $i+1; $j < count($in) && $in[$j]{0} == '*'; $j++);

            $lines = array_slice($in, $i, $j-$i);
            $lines = implode("\n", $lines);

            array_push($items, $this->fmt_sublist($this->parse_list($lines)));

            $i = $j;
        }
        return $items;
    }

    protected function parse_block($in)
    {
        if (empty($in))
            return NULL;
        if ($in{0} == '*')
            return $this->fmt_list($this->parse_list($in));
        if ($in{0} == '|')
            return $this->fmt_par('[table]');
        /* heading */
        if (preg_match('/^ (={2,}) \s* (.+?) =* $/xm', $in, $m))
        {
            $r = array($this->fmt_heading(strlen($m[1])-1, $this->parse_par($m[2])));
            $pos = strpos($in, "\n");
            if ($pos !== FALSE)
                array_push($r, $this->parse_block(substr($in, $pos+1)));
            return $r;
        }
        return $this->fmt_par($this->parse_par($in));
    }

    protected function parse_page($in)
    {
        $lines = explode("\n", $in);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines, array('eMarkup', 'comment_filter'));
        $lines = implode("\n", $lines);
        $blocks = preg_split(';\n{2,};', $lines);
        $blocks = array_map(array($this, 'parse_block'), $blocks);
        $blocks = array_flatten($blocks);
        return $this->fmt_page($blocks);
    }

    public function format()
    {
        return $this->parse_page($this->content);
    }
}

// vim:set fdm=marker fmr={{{,}}}:

?>
