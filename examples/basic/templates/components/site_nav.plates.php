<?php namespace ProcessWire;
/**
 * @property string|null $title  Optional gallery title
 * @property Pageimages  $images Images field value
 */

$rootPage = $pages->get('/');
?>
<ul>
  <?php foreach ($rootPage->children->prepend($rootPage) as $navPage): ?>
    <li class="<?=$navPage->id === $page->id ? 'active' : ''?>">
      <a href="<?=$navPage->url?>"><?=$navPage->title?></a>
      <?php if (count($navPage->children)): ?>
        <ul>
          <?php foreach ($navPage->children as $navPageChild): ?>
            <li  class="<?=$navPage->id === $page->id ? 'active' : ''?>">
              <a href="<?=$navPageChild->url?>"><?=$navPageChild->title?></a>
            </li>
          <?php endforeach ?>
        </ul>
      <?php endif ?>
    </li>
  <?php endforeach ?>
</ul>