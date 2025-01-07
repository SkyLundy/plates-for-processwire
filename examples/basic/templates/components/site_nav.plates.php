<?php namespace ProcessWire;
/**
 * @property string|null $basePageSelector
 */

$basePage = $pages->get($basePageSelector ?? '/');
?>
<ul>
  <?php foreach ($basePage->children->prepend($basePage) as $navPage): ?>
    <?php $this->insert('components::site_nav_item', ['navPage' => $navPage]) ?>
  <?php endforeach ?>
</ul>