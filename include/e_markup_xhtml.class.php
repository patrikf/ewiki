<?php

require_once('e_markup.class.php');

class eMarkupXHTML extends eMarkup
{
    protected function fmt_par($s)      { return sprintf('<p>%s</p>', $s); }
    protected function fmt_heading($level, $s)
                                        { return sprintf('<h%d>%s</h%1$d>', $level+1, $s); }
    protected function fmt_list($items) { return sprintf('<ul>%s</ul>', join('', $items)); }
    protected function fmt_listitem($s) { return sprintf('<li>%s</li>', $s); }
    protected function fmt_table($rows) { return sprintf('<table>%s</table>', join('', $rows)); }
    protected function fmt_row($cells)  { return sprintf('<tr>%s</tr>', join('', $cells)); }
    protected function fmt_cell($s)     { return sprintf('<td>%s</td>', $s); }
    protected function fmt_emph($s)     { return sprintf('<emph>%s</emph>', $s); }
    protected function fmt_strong($s)   { return sprintf('<strong>%s</strong>', $s); }
    protected function fmt_labeled_link($url, $label)
                                        { return sprintf('<a href="%s">%s</a>', $url, $label); }
}

?>
