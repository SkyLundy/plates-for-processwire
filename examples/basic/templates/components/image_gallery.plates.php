<?php namespace ProcessWire;
/**
 * @property string|null      $title  Optional gallery title
 * @property Pageimages|null  $images Images field value
 */
?>
<div class="image-gallery">
  <?php if ($title ?? false): ?>
    <h2><?=$title?></h2>
  <?php endif ?>
  <ul>
    <?php if ($images ?? false): ?>
      <?php foreach ($images as $image): ?>
        <li>
          <img src="<?=$image->url?>" alt="<?=$image->description?>">
        </li>
      <?php endforeach ?>
    <?php else: ?>
      <li><?=__('There are no images available yet to view')?></li>
    <?php endif ?>
  </ul>
</div>