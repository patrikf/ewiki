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
    protected function fmt_cell_head($s){ return sprintf('<th>%s</th>', $s); }
    protected function fmt_emph($s)     { return sprintf('<emph>%s</emph>', $s); }
    protected function fmt_strong($s)   { return sprintf('<strong>%s</strong>', $s); }
    protected function fmt_labeled_link($url, $label, $new=FALSE)
                                        { return sprintf('<a href="%s"%s>%s</a>', $url, $new?' class="new"':'', $label); }

    protected function fmt_plain($s)    { return Markup::escape($s); }
    protected function fmt_error($s)    { return sprintf('<div class="error">%s</div>', $s); }

    /* specific to eWiki */
    protected function fmt_image($ref, $width, $height)
    {
        if (!$width && !$height)
        {
            $width = Config::IMAGE_WIDTH;
            $height = Config::IMAGE_HEIGHT;
        }
        try
        {
            $page = new WikiPage($ref);
        }
        catch (Exception $e)
        {
            return $this->fmt_error('No such file: '.$this->mklink($ref));
        }
        if ($page->getPageType() != WikiPage::TYPE_IMAGE)
            return $this->fmt_error('Not an image: '.$this->mklink($ref));

        $url = $page->getURL();
        return "<div class=\"par image\"><a href=\"$url\"><img src=\"$url?action=image&amp;width=$width&amp;height=$height\" alt=\"{$page->getName()}\" /></a></div>";
    }

    protected function mklink($ref, $label=NULL)
    {
        list($url, $label2, $valid) = Markup::parseLinkTarget($ref);
        if ($label === NULL)
            $label = $label2;
        return $this->fmt_labeled_link($url, $label, !$valid);
    }
}

?>
