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

    protected function uint16_at($pos) { return Binary::uint16($this->cache, $pos); }
    protected function uint32_at($pos) { return Binary::uint32($this->cache, $pos); }
    protected function nuint32_at($pos, $n) { return Binary::nuint32($n, $this->cache, $pos); }

    public function __construct($mime_cache=null)
    {
        if ($mime_cache === null)
        {
            if (defined('Config::MIME_CACHE_PATH'))
            {
                $mime_cache = Config::MIME_CACHE_PATH;
            }
            else
            {
                $mime_cache = '/usr/share/mime/mime.cache';
            }
        }
        if (!is_readable($mime_cache))
        {
            throw new Exception('unable to read mime.cache file');
        }
        
        $this->cache = file_get_contents($mime_cache);
        $this->version = sprintf('%d.%d', $this->uint16_at(0), $this->uint16_at(2));

        if ($this->version == '1.0')
        {
            $this->literal_size = 8;
            $this->glob_size = 8;
        }
        else if ($this->version == '1.1')
        {
            $this->literal_size = 12;
            $this->glob_size = 12;
        }
        else
            throw new Exception('unsupported mime.cache version '.$this->version);
    }

    protected function literal($filename)
    {
        $pos = $this->uint32_at(12);
        $n = $this->uint32_at($pos);

        /* binary search */
        $pos += 4;
        $a = 0;
        $span = $n;
        while ($span > 0)
        {
            $cur = $a + (int)($span/2);
            list($lit_off, $type_off) = $this->nuint32_at($pos + $this->literal_size*$cur, 2);
            $c = strcmp($filename, $this->string_at($lit_off));
            if ($c == 0)
                return $this->string_at($type_off);
            else if ($span == 1)
                break;
            else if ($c < 0)
                $span = (int)($span/2);
            else
            {
                $a += (int)($span/2) + 1;
                $span = (int)($span/2) - !($span%2);
            }
        }
        return NULL;
    }

    protected function suffix($filename)
    {
        if ($this->version == '1.0')
        {
            /* forward suffix tree */
            $queue = array(array('', $this->uint32_at(16)));

            while (!empty($queue))
            {
                list($suffix, $pos) = array_shift($queue);
                list($n, $pos) = $this->nuint32_at($pos, 2);

                for ($i = 0; $i < $n; $i++, $pos += 16)
                {
                    $c = $this->uint32_at($pos);
                    $cur = $suffix . chr($c);

                    $idx = strrpos($filename, $cur);
                    if ($idx === FALSE)
                        continue;
                    else if ($idx + strlen($cur) == strlen($filename))
                    {
                        $type_off = $this->uint32_at($pos+4);
                        if ($type_off == 0)
                            continue;
                        return $this->string_at($type_off);
                    }
                    else
                        $queue[] = array($cur, $pos+8);

                    $pos += 16;
                }
            }
        }
        else
        {
            /* reverse suffix tree */
            list($n, $pos) = $this->nuint32_at($this->uint32_at(16), 2);
            $suffix = '';

            for ($i = 0; $i < $n;)
            {
                $c = $this->uint32_at($pos);
                if ($c == 0)
                {
                    $type_off = $this->uint32_at($pos+4);
                    return $this->string_at($type_off);
                }
                else if ($filename{strlen($filename)-strlen($suffix)-1} == chr($c))
                {
                    $suffix = chr($c).$suffix; /* FIXME: make unicode-aware? */
                    if (strlen($suffix) >= strlen($filename))
                        break;
                    list($n, $pos) = $this->nuint32_at($pos+4, 2);
                    $i = 0;
                }
                else
                {
                    $i++;
                    $pos += 12;
                }
            }
        }
        return NULL;
    }

    protected function glob($filename)
    {
        $pos = $this->uint32_at(20);
        $n = $this->uint32_at($pos);

        $pos += 4;
        for ($i = 0; $i < $n; $i++, $pos += $this->glob_size)
        {
            list($glob_off, $type_off) = $this->nuint32_at($pos, 2);
            $glob = $this->string_at($glob_off);
            if (fnmatch($glob, $filename))
                return $this->string_at($type_off);
        }
        return NULL;
    }

    protected static function byteswap(&$a, $word)
    {
        /*
         * FIXME: add check for big-endian machines, which do not need
         * swapping
         */
        for ($i = 0; $i < count($a); $i += $word)
            array_splice($a, $i, $word, array_reverse(array_slice($a, $i, $word)));
    }

    protected function bufMatchlet(&$buf, $pos)
    {
        list($off, $range, $word_size, $len, $value_off, $mask_off, $n_child, $child_off)
            = $this->nuint32_at($pos, 8);

        $range = min($range, strlen($buf)-$len-$off+1);
        if ($range <= 0)
            return FALSE;

        $value = array_map('ord', str_split($this->substr($value_off, $len)));
        if ($mask_off)
            $mask = array_map('ord', str_split($this->substr($mask_off, $len)));
        else
            $mask = NULL;

        assert(($len % $word_size) == 0);
        if ($word_size > 1)
        {
            self::byteswap($value, $word_size);
            if ($mask)
                self::byteswap($mask, $word_size);
        }

        for ($start = $off; $start < $off+$range; $start++)
        {
            for ($i = 0; $i < $len; $i++)
            {
                $c = ord($buf{$start+$i});
                if ($mask)
                    $c &= $mask[$i];
                if ($c != $value[$i])
                    continue 2;
            }
            return $this->bufMatchlets($buf, $child_off, $n_child);
        }
        return FALSE;
    }

    protected function bufMatchlets(&$buf, $pos, $n)
    {
        $m = TRUE;
        for ($j = 0; $j < $n; $j++, $pos += 32)
        {
            $m = $this->bufMatchlet($buf, $pos);
            if ($m)
                break;
        }
        return $m;
    }

    protected function bufMagic(&$buf, $pri_min=0, $pri_max=-1)
    {
        $pos = $this->uint32_at(24);
        list($n, $max_extent, $pos) = $this->nuint32_at($pos, 3);

        for ($i = 0; $i < $n; $i++, $pos += 16)
        {
            list($pri, $type_off, $n_matchlets, $matchlets_off) = $this->nuint32_at($pos, 4);
            /* entries are sorted by priority */
            if ($pri < $pri_min)
                break;
            if ($pri_max >= 0 && $pri > $pri_max)
                continue;

            if ($this->bufMatchlets($buf, $matchlets_off, $n_matchlets))
                return $this->string_at($type_off);
        }
    }

    protected function bufGuessBinary(&$buf)
    {
        for ($i = 0; $i < 32 && $i < strlen($buf); $i++)
        {
            $c = ord($buf{$i});
            if ($c < 0x20 && $c != 0x09 /* \t */ && $c != 0x0A /* \n */)
                return TRUE;
        }
        return FALSE;
    }

    public function bufferGetType($buf, $filename=NULL)
    {
        if ($filename)
            $filename = basename($filename);

        if ($filename)
        {
            $r = $this->bufMagic($buf, 80);
            if ($r)
                return $r;

            $r = $this->literal($filename);
            if ($r)
                return $r;
            $r = $this->suffix($filename);
            if ($r)
                return $r;
            $r = $this->glob($filename);
            if ($r)
                return $r;

            $is_binary = $this->bufGuessBinary($buf);
            /*
             * Checking all magic signatures is a very expensive operation.
             * We trade off some accuracy for faster text/plain recognition.
             */
            if ($is_binary)
            {
                $r = $this->bufMagic($buf, 0, 79);
                if ($r)
                    return $r;
            }
        }
        else
        {
            $is_binary = $this->bufGuessBinary($buf);
            if ($is_binary)
                $r = $this->bufMagic($buf);
            else
                $r = $this->bufMagic($buf, 80);
            if ($r)
                return $r;
        }
        return $is_binary ? 'application/octet-stream' : 'text/plain';
    }
}

?>
