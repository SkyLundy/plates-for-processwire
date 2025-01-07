<?php namespace ProcessWire;
/**
 * Using a docblock to specify data that is expected when using a file in Plates template is not
 * required, but it may be considered good practice for organization and clarity
 *
 * @property string|null $title       Page title
 * @property string|null $description Meta description
 *
 * $this->if() - Provided by Conditionals extension
 * $this->preloadAssets() - Provided by Asset Loader extension
 * $this->preloadAsset() - Provided by Asset Loader extension
 * $this->linkAsset() - Provided by Asset Loader extension
 * $this->inlineAsset() - Provided by Asset Loader extension
 */
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title ?? $page->title?></title>
    <?=$this->if($description ?? false, "<meta name='description' content='{$description}'>")?>
    <?=$this->preloadAssets([
      'fonts::ProximaNova.woff2',
      'fonts::ProximaNovaLight.woff2',
    ])?>
    <?=$this->preloadAsset('js::app.js')?>
    <?=$this->linkAsset('styles::app.css')?>
    <?=$this->inlineAsset('styles::critical.css')?>
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
    <?=$this->linkAsset('js::app.js')?>
  </body>
</html>


