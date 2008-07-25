<?php

require('include/core.class.php');

$timer = array();
$count = array();

function start($x)
{
    global $timer, $timer_cur, $count;
    if (!isset($timer[$x]))
        $timer[$x] = $count[$x] = 0;
    $timer_cur[$x] = microtime(TRUE);
}

function stop($x)
{
    global $timer, $timer_cur, $count;
    $timer[$x] += microtime(TRUE) - $timer_cur[$x];
    $count[$x]++;
}

class Diff
{
    /*
     * Diff::subLevenshtein
     *
     * Returns: an array, with the element with index i giving the Levenshtein
     * distance between $b and the first i elements of $a, that is, the last
     * line of the matrix used in the Needleman-Wunsch algorithm.
     */
    static public function subLevenshtein($a, $b)
    {
        $line = array();
        for ($j = 0; $j <= count($a); $j++)
            $line[$j] = $j;

        for ($i = 1; $i <= count($b); $i++)
        {

            /* down */
            $line[0]++;

            $prev = $line[0];
            for ($j = 1; $j <= count($a); $j++)
            {
                /* down */
                $min = $line[$j] + 1;

                /* right */
                $cur = $line[$j-1]+1;
                if ($cur < $min)
                    $min = $cur;

                /* down-right */
                $cur = $prev + ($a[$j-1] != $b[$i-1]);
                if ($cur < $min)
                    $min = $cur;

                $prev = $line[$j] = $min;
            }
        }

        return $line;
    }

    /*
     * Diff::LCS
     *
     * Calculate the Longest Common Subsequence (using Hirschberg's algorithm)
     */
    static public function LCS($a, $b)
    {
        if (count($a) > count($b))
        {
            $tmp =& $a;
            $a =& $b;
            $b =& $tmp;
        }

        if (count($a) == 0)
            return array();
        if (count($a) == 1)
            return array_intersect($a, $b);

        /* divide the larger array ($b) */
        $split = (int)(count($b)/2);
        $b1 = array_slice($b, 0, $split);
        $b2 = array_slice($b, $split);

        /* find optimal split point for $a */
        start('lev1');
        $lev1 = self::subLevenshtein($a, $b1);
        stop('lev1');
        start('lev2');
        $lev2 = array_reverse(self::subLevenshtein(array_reverse($a), array_reverse($b2)));
        stop('lev2');

        $split = 0;
        $split_lev = -1;
        for ($i = 0; $i <= count($a); $i++)
        {
            $lev = $lev1[$i] + $lev2[$i];
            if ($split_lev == -1 || $lev < $split_lev)
            {
                $split = $i;
                $split_lev = $lev;
            }
        }

        $a1 = array_slice($a, 0, $split);
        $a2 = array_slice($a, $split);

        $r = array_merge(self::LCS($a1, $b1), self::LCS($a2, $b2));
        return $r;
    }
}

$strings = array();
$strings_r = array();
function index($x)
{
    global $strings;
    if (!isset($strings[$x]))
    {
        $strings[$x] = count($strings);
        $strings_r[$strings[$x]] = $x;
    }
    return $strings[$x];
}
function unindex($x)
{
    global $strings_r;
    return $strings_r[$x];
}

$a = file($argv[1], FILE_IGNORE_NEW_LINES);
$b = file($argv[2], FILE_IGNORE_NEW_LINES);

//$a = array_map('index', $a);
//$b = array_map('index', $b);
//echo "mapped\n";
$lcs = Diff::LCS($a, $b);
print_r($count);
print_r($timer);
exit(0);

for ($ia = $ib = $ic = 0; $ic < count($lcs); $ia++, $ib++, $ic++)
{
    for (; $a[$ia] != $lcs[$ic]; $ia++)
        echo "-".$a[$ia]."\n";
    for (; $b[$ib] != $lcs[$ic]; $ib++)
        echo "+".$b[$ib]."\n";
    echo " ".$lcs[$ic]."\n";
}
for (; $ia < count($a); $ia++)
    echo "-".$a[$ia]."\n";
for (; $ib < count($b); $ib++)
    echo "+".$b[$ib]."\n";

?>
