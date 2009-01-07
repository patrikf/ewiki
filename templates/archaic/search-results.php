<? $title = sprintf('Search results: %s', $query); ?>
<? include('header.php'); ?>
<h1 id="pagetitle"><?= Markup::escape($title); ?></h1>
<div class="searchresults">
<? if (!count($results)): ?>
  <p>Sorry, your search yielded no results.</p>
<? endif; ?>
<? foreach ($results as $result): ?>
  <div class="result">
    <div class="page"><?= $result->page->link(); ?></div>
    <? if (!count($result->matches)): ?>
      no matches in file content
    <? elseif ($result->page->getPageType() != WikiPage::TYPE_PAGE): ?>
      match in binary data
    <? else: ?>
      <? foreach ($result->matches as $match): ?>
        <div class="match">
          <?
          $css_classes = array('before-match', 'match', 'after-match');
          for ($i = 0; $i < 3; $i++)
              echo sprintf('<span class="%s">%s</span>', $css_classes[$i], Markup::escape($match->env[$i]));
          ?>
        </div>
      <? endforeach; ?>
    <? endif; ?>
  </div>
<? endforeach; ?>
</div>
<? include('footer.php'); ?>
