<?php namespace ProcessWire;
/**
 * $this->ifPage() - Provided by the Conditionals extension
 */

?>
<li>
  <a href="<?=$navPage->url?>" class="<?=$this->ifPage($navPage, 'active')?>">
    <?=$navPage->title?>
  </a>

  <?php if (!$navPage->is('/') && $navPage->numChildren()): ?>
    <ul>
      <?php foreach ($navPage->children as $childPage): ?>
        <?php $this->insert('components::site_nav_item', ['navPage' => $childPage]) ?>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
</li>