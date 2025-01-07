<?php namespace ProcessWire;

/**
 * $this->if() - Provided by the Conditionals extension
 * $this->insertIf() - Provided by the Conditionals extension
 * $this->stripHtml() - Provided by Functions extension (shown batched)
 */

$this->layout('layouts::main', ['description' => $page->description]);
?>
<?php $this->start('hero') ?>
  <h1><?=$page->headline?></h1>

  <?=$this->if($page->headline2, "<h2>{$page->headline2}</h2>")?>
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
  <?php $this->insertIf('components::image_gallery', $page->image_gallery, [
    'title' => __('Event Gallery'),
    'images' => $page->image_gallery,
  ]) ?>
</section>

<section>
  <ul class="team-members">
    <?php foreach ($pages->get('template=team')->children as $person): ?>
      <li>
        <span><?=__('Name')?>: <?=$this->batch($person->title, 'trim|strtolower|ucwords')?></span>
        <span><?=__('Title')?>: <?=$this->batch($person->job_title, 'trim|strtolower|ucwords')?></span>
        <span><?=__('Bio')?>: <?=$this->batch($person->bio, 'stripHtml|trim')?></span>
      </li>
    <?php endforeach ?>
  </ul>
</section>

<section>
  <?=__('Congratulations To Our Contest Winners')?>
  <ul>
    <?php foreach ($this->toList($page->contest_winners) as $i => $image): ?>
      <li>
        <?=$this->if($i === 0, __('Grand Prize Winner')) ?>
        <img src="<?=$image->url?>" alt="<?=$image->description?>">
      </li>
    <?php endforeach ?>
  </ul>
</section>

<?php $this->start('footer') ?>
  <?php $this->insert('components::contact_form') ?>
<?php $this->end() ?>