<?php namespace ProcessWire;

$rootPage = $pages->get('/');
?>
<ul>
  <?php foreach ($rootPage->children->prepend($rootPage) as $navPage): ?>
    <?php $this->insert('components::site_nav_item', ['navPage' => $navPage]) ?>
  <?php endforeach ?>
</ul>