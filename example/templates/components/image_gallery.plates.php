<?php namespace ProcessWire;
/**
 * @property string|null      $title  Optional gallery title
 * @property Pageimages|null  $images Images field value
 */

$title ??= null;
?>
<div>
  <?php if ($title): ?>
    <h2><?=$title?></h2>
  <?php endif ?>
  <ul>
    <?php foreach ($images as $image): ?>
      <li><img src="<?=$image->url?>" alt="<?=$image->description?>"></li>
    <?php endforeach ?>
  </ul>
</div>


<!--
  If the Conditionals Extension is enabled, the above could alternatively be written as:
-->
<?php namespace ProcessWire;
/**
 * @property string|null      $title  Optional gallery title
 * @property Pageimages|null  $images Images field value
 */
?>
<div>
  <?=$this->if($title ?? false, "<h2>{$title}</h2>")?>
  <ul>
    <?php foreach ($images as $image): ?>
      <li><img src="<?=$image->url?>" alt="<?=$image->description?>"></li>
    <?php endforeach ?>
  </ul>
</div>