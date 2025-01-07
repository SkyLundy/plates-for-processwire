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

  <?php if ($navPage->id !== 1 && count($navPage->children)): ?>
    <ul>
      <?php foreach ($navPage->numChildren() as $childPage): ?>
        <?php $this->insert('components::site_nav_item', ['navPage' => $childPage]) ?>
      <?php endforeach ?>
    </ul>
  <?php endif ?>

</li>