<?php namespace ProcessWire;
/**
 * Recursively builds child page tree items by rendering itself if there are child pages present
 *
 * @property Page $navPage
 */
?>
<li>
  <a href="<?=$navPage->url?>" class="<?=$navPage->id === $page->id ? 'active' : ''?>">
    <?=$navPage->title?>
  </a>
  <?php if (!$navPage->match(1) && $navPage->numChildren()): ?>
    <ul>
      <?php foreach ($navPage->numChildren() as $childPage): ?>
        <?php $this->insert('components::navigation_item', ['navPage' => $childPage]) ?>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
</li>


<!--
  If the Conditionals Extension is enabled, the link above could be written as:
-->
<a href="<?=$navPage->url?>"<?=$this->attrIfPage($navPage, 'class', 'active')?>>
  <?=$navPage->title?>
</a>