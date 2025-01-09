<?php namespace ProcessWire;

$this->layout('layouts::main', ['description' => $page->description]);
?>

<?php $this->start('hero') ?>
  <h1><?=$page->headline?></h1>
<?php $this->end() ?>

<!--
  Render an image gallery located in the 'components' folder as configured in ready.php
-->
<?php if ($page->gallery->count()): ?>
  <?php $this->insert('components::image_gallery', [
    'images' => $page->gallery,
    'title' => __('Image Gallery'),
  ]) ?>
<?php endif ?>

<!--
  insertIf() is available if the optional Conditionals Extension packaged with this module is enabled
-->
<?php $this->insertIf('components::image_gallery', $page->gallery->count(), [
  'images' => $page->gallery,
  'title' => __('Image Gallery')
]) ?>

<!--
  If you prefer the fetch() Plates syntax, the Conditionals Extension also provides fetchIf()
-->
<?=$this->fetchIf('components::image_gallery', $page->gallery->count(), [
  'images' => $page->gallery,
  'title' => __('Image Gallery')
]) ?>

<?php $this->start('footer') ?>
  <?php $this->insert('components::newsletter_signup_form') ?>
<?php $this->end() ?>