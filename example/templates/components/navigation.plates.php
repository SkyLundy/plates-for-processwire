<?php namespace ProcessWire;
/**
 * @property string          $ariaLabel
 * @property string|int|null $basePage
 */

$basePage = $pages->get($basePage ?? '/');
?>
<nav aria-label="<?=$ariaLabel?>">
  <ul>
    <?php foreach ($basePage->children->prepend($basePage) as $navPage): ?>
      <?php $this->insert('components::navigation_item', ['navPage' => $navPage]) ?>
    <?php endforeach ?>
  </ul>
</nav>



<!--
  If the Functions Extension is enabled the above could alternatively be written using the
  withChildren() function as:
-->
<?php namespace ProcessWire;
/**
 * @property string               $ariaLabel
 * @property Page|string|int|null $basePage
 */
?>
<nav aria-label="<?=$ariaLabel?>">
  <ul>
    <?php foreach ($this->withChildren($basePage ?? '/') as $navPage): ?>
      <?php $this->insert('components::navigation_item', ['navPage' => $navPage]) ?>
    <?php endforeach ?>
  </ul>
</nav>



<!--
  The withChildren() function also accepts a selector for children for additional flexibility
-->
<?php namespace ProcessWire;
/**
 * @property string               $ariaLabel
 * @property Page|string|int|null $basePage
 * @property string|int|null $childSelector
 */
?>
<nav aria-label="<?=$ariaLabel?>">
  <ul>
    <?php foreach ($this->withChildren($basePage ?? '/', $childSelector ?? null) as $navPage): ?>
      <?php $this->insert('components::navigation_item', ['navPage' => $navPage]) ?>
    <?php endforeach ?>
  </ul>
</nav>