<?php namespace ProcessWire;
/**
 * @property string|int|null $basePageSelector Base page to build navigation tree from
 *
 * $this->withChildren() - Provided by the Functions extension
 */
?>
<ul>
  <?php foreach ($this->withChildren($basePageSelector ?? '/') as $navPage): ?>
    <?php $this->insert('components::site_nav_item', ['navPage' => $navPage]) ?>
  <?php endforeach ?>
</ul>