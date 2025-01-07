<?php namespace ProcessWire;
/**
 * $this->ifPage() - Provided by the Conditionals extension
 * $this->wrapIf() - Provided by the Conditionals extension
 * $this->withChildren() - Provided by the Functions extension
 */

?>
<ul>
  <?php foreach ($this->withChildren('/') as $navPage): ?>
    <?php $this->insert('components::site_nav_item', ['navPage' => $navPage]) ?>
  <?php endforeach ?>
</ul>