<?php

class MIME
{
    protected $cache;
    protected $version;
    protected $offset;

    protected function substr($pos, $len)
    {
        return substr($this->cache, $pos, $len);
    }

    protected function string_at($pos)
    {
        $end = strpos($this->cache, "\0", $pos);
        return $this->substr($pos, $end-$pos);
    }

    protected function uint16_at($pos)
    {
        return ord($this->cache{$pos+0}) << 8 | ord($this->cache{$pos+1});
    }

    protected function uint32_at($pos)
    {
        return ord($this->cache{$pos+0}) << 24 | ord($this->cache{$pos+1}) << 16 | ord($this->cache{$pos+2}) << 8 | ord($this->cache{$pos+3});
    }

    protected function nuint32_at($pos, $n)
    {
        $r = array();
        for ($i = 0; $i < $n; $i++, $pos += 4)
            array_push($r, $this->uint32_at($pos));
        return $r;
    }

    public function __construct($mime_cache='/usr/share/mime/mime.cache')
    {
        $this->cache = file_get_contents($mime_cache);
        $this->version[0] = $this->uint16_at(0);
        $this->version[1] = $this->uint16_at(2);

        assert($this->version[0] == 1 && $this->version[1] == 0);
    }

    protected function literal($filename)
    {
        return NULL;
    }

    protected function glob($filename)
    {
        return NULL;
    }

    protected function buf_matchlet(&$buf, $pos)
    {
        list($range_off, $range_len, $word_len, $value_len, $value_off, $mask_off, $n_child, $child_off)
            = $this->nuint32_at($pos, 8);

        printf("matchlet: value_len(%d) range_len(%d)\n", $value_len, $range_len);
        return FALSE;

        return $this->buf_matchlets($buf, $child_off, $n_child);
    }

    protected function buf_matchlets(&$buf, $pos, $n)
    {
        $m = TRUE;
        for ($j = 0; $j < $n; $j++, $pos += 32)
        {
            $m = $this->buf_matchlet($buf, $pos);
            if ($m)
                break;
        }
        return $m;
    }

    protected function buf_magic(&$buf, $pri_min=0, $pri_max=-1)
    {
        $pos = $this->uint32_at(24);
        list($n, $max_extent, $pos) = $this->nuint32_at($pos, 3);

        $r = array();
        for ($i = 0; $i < $n; $i++, $pos += 16)
        {
            list($pri, $type_off, $n_matchlets, $matchlets_off) = $this->nuint32_at($pos, 4);
            /* entries are sorted by priority */
            if ($pri < $pri_min)
                break;
            if ($pri_max >= 0 && $pri > $pri_max)
                continue;

            if ($this->buf_matchlets($buf, $matchlets_off, $n_matchlets))
                array_push($r, array($pri, $this->string_at($type_off)));
        }
        return $r;
    }

    public function buffer_get_type($buf, $filename=NULL)
    {
        if ($filename)
            $filename = basename($filename);

        if ($filename)
        {
            $r = $this->buf_magic($buf, 80);
            if (count($r))
                return $r[0][1];

            $r = $this->literal($filename);
            if ($r)
                return $r;
            $r = $this->glob($filename);
            if ($r)
                return $r;

            $r = $this->buf_magic($buf, 0, 79);
            if (count($r))
                return $r[0][1];
        }
        else
        {
            $r = $this->buf_magic($buf);
            if (count($r))
                return $r[0][1];
        }
    }
};

?>
