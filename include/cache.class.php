<?php

final class Cache
{
    static public function httpdate($timestamp=NULL)
    {
        if (!$timestamp)
            $timestamp = time();
        return gmdate('D, d M Y H:i:s', $timestamp).' GMT';
    }

    static public function cache($last_modified=NULL, $if_modified_since=NULL)
    {
        if (!$last_modified)
            $last_modified = time();
        if (!$if_modified_since && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
            $if_modified_since = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if ($if_modified_since && $if_modified_since >= $last_modified)
        {
            // client has some recent version
            header('Status: 304 Not Modified');
            exit();
        }
        else
            header('Last-Modified: ' . Cache::httpdate($last_modified));
    }
};

?>
