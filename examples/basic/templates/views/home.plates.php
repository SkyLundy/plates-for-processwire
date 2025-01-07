<?php namespace ProcessWire;

$this->layout('layouts::main', ['description' => $page->description]);
?>
<?php $this->start('hero') ?>
  <h1><?=$page->headline?></h1>

  <?=$page->headline2 ? "<h2>{$page->headline2}</h2>" : null?>
<?php $this->end() ?>

<section>
  <div class="text-content">
    <?=$page->body?>
  </div>
  <div class="image-content">
    <img src="<?=$page->image->url?>" alt="<?=$page->image->description?>">
  </div>
</section>

<section>
  <?php $this->insert('components::image_gallery', [
    'title' => __('Event Gallery'),
    'images' => $page->image_gallery
  ]) ?>
</section>

<section>
  <ul class="team-members">
    <?php foreach ($pages->get('template=team')->children as $person): ?>
      <li>
        <span><?=__('Name')?>: <?=$this->batch($person->title, 'trim|strtolower|ucwords')?></span>
        <span><?=__('Title')?>: <?=$this->batch($person->job_title, 'trim|strtolower|ucwords')?></span>
        <span><?=__('Bio')?>: <?=$sanitizer->markupToText(trim($person->bio))?></span>
      </li>
    <?php endforeach ?>
  </ul>
</section>

<section>
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