<?php namespace ProcessWire;
/**
 * Using a docblock to specify data that is expected when using a file in Plates template is not
 * required, but it may be considered good practice for organization and clarity
 *
 * @property string|null $title       Page title
 * @property string|null $description Meta description
 */
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? $page->title; ?></title>
    <?php if ($description): ?>
      <meta name="description" content="<?=$description?>">
    <?php endif ?>
    <link rel="preload" href="<?=$config->paths->templates?>fonts/ProximaNova.woff2" as="font" crossorigin>
    <link rel="preload" href="<?=$config->paths->templates?>fonts/ProximaNovaLight.woff2" as="font" crossorigin>
    <link rel="preload" href="<?=$config->paths->templates?>scripts/app.js" as="script">
    <link rel="stylesheet" href="<?=$config->paths->templates?>styles/app.css">
    <style>
      <?=$files->fileGetContents("{$config->paths->templates}styles/critical.css")?>
    </style>
  </head>
  <body>
    <header class="site-header">
      <a href="<?=$pages->get('/')->url?>">
        <img src="<?=$config->paths->templates?>images/logo.jpg" alt="<?=__('Our Logo')?>">
      </a>
      <nav>
        <?php $this->insert('components::site_nav'); ?>
      </nav>
    </header>
    <section>
      <?= $this->section('page_hero'); ?>
    </section>

    <?= $this->section('content') ?>

    <footer class="site-footer">
      <?= $this->section('page_footer'); ?>
      <nav>
        <?php $this->insert('components::site_nav'); ?>
      </nav>
    </footer>
    <script src="<?=$config->paths->templates?>scripts/app.js"></script>
  </body>
</html>


