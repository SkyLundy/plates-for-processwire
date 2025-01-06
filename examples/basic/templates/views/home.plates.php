<?php namespace ProcessWire;

$this->layout('layouts::main', ['description' => $page->description]);
?>
<?php $this->start('hero') ?>
  <h1><?=$page->headline?></h1>

  <?php if ($page->headline2): ?>
    <h2><?=$page->headline2?></h2>
  <?php endif ?>
<?php $this->end() ?>

<section class="introduction">
  <div class="text-content">
    <?=$page->body?>
  </div>
  <div class="image-content">
    <img src="<?=$page->image->url?>" alt="<?=$page->image->description?>">
  </div>
</section>

<section class="event-images">
  <?php $this->insert('components::image_gallery', [
    'title' => __('Event Gallery'),
    'images' => $page->image_gallery
  ]) ?>
</section>

<section class="about-us">
  <div class="text-content">
    <?=$page->body2?>
  </div>
  <ul class="team-members">
    <?php foreach ($pages->get('template=team')->children as $person): ?>
      <li>
        <span>Name: <?=$this->batch($person->title, 'trim|strtolower|ucwords')?></span>
        <span>Title: <?=$this->batch($person->job_title, 'trim|strtolower|ucwords')?></span>
        <span>Bio: <?=$sanitizer->markupToText(trim($person->bio))?></span>
      </li>
    <?php endforeach ?>
  </ul>
</section>

<section class="contest-winners">
  <h2><?=__('Congratulations To Our Contest Winners')?></h2>
  <ul>
    <?php foreach ($page->contest_winners as $image): ?>
      <li>
        <?=$image === $page->contest_winners->first() ? __('Grand Prize Winner') : ''?>
        <img src="<?=$image->url?>" alt="<?=$image->description?>">
      </li>
    <?php endforeach ?>
  </ul>
</section>

<?php $this->start('footer') ?>
  <?php $this->insert('components::contact_form') ?>
<?php $this->end() ?>