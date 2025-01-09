<?php namespace ProcessWire;
/**
 * @property string|null $title      Page title
 * @property string|null $decription Meta description
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
    <title><?= $title ?? $page->title; ?></title>
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
    <header>
      <a href="<?=$pages->get('/')->url?>">
        <img src="<?=$config->paths->templates?>static_images/logo.jpg" alt="<?=__('Our Logo')?>">
      </a>

      <?php $this->insert('components::navigation', ['ariaLabel' => __('Main')]); ?>
    </header>

    <section>
      <?= $this->section('page_hero'); ?>
    </section>

    <?= $this->section('content') ?>

    <footer class="site-footer">
      <?= $this->section('page_footer'); ?>

      <?php $this->insert('components::navigation', ['ariaLabel' => __('Footer')]); ?>
    </footer>
    <?=$this->linkAsset('js::app.js')?>
  </body>
</html>


