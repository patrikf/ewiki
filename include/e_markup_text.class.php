<?php

require_once('e_markup.class.php');

class eMarkupText extends eMarkup
{
    protected function fmt_par($s) { return $s."\n\n"; }
    protected function fmt_heading($level, $s)
    {
        if ($level == 1 || $level == 2)
            return $s."\n".str_repeat($level == 1 ? '=' : '-', strlen($s))."\n\n";
        if ($level == 3)
            return $s.":\n\n";
    }
    protected function fmt_list($items) { return implode('', $items)."\n"; }
    protected function fmt_listitem($s) { return "*".$s."\n"; }
    protected function fmt_table($rows) { return implode('', $rows)."\n"; }
    protected function fmt_row($cells)  { return "| ".implode(" | ", $cells)." |\n"; }
    protected function fmt_cell($s)     { return $s; }
    protected function fmt_emph($s)     { return "_".$s."_"; }
    protected function fmt_strong($s)   { return "*".$s."*"; }
    protected function fmt_link($url, $label) { return $label; }
    protected function fmt_labeled_link($url, $label) { return sprintf("'%s' [%s]", $label, $url); }
}

?>
