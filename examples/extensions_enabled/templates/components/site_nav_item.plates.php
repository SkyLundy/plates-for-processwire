<?php namespace ProcessWire;
/**
 * Recursively builds child page tree items by rendering itself if there are child pages present
 *
 * @property Page $navPage
 *
 * $this->attrIfPage() - Provided by the Conditionals extension
 */
?>
<li>
  <a href="<?=$navPage->url?>" <?=$this->attrIfPage($navPage, 'class', 'active')?>>
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