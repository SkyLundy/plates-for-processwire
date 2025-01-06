# Conditionals Extension

A custom Plates extension that provides methods to supplement the [alternate control structures](https://www.php.net/manual/en/control-structures.alternative-syntax.php) in PHP.

## attrIf

Outputs an attribute if conditional is truthy. Second argument is the attribute. Optional third argument is the attribute value.

Note that the space before the opening `<?=` is ommitted. Attributes are automatically padded with a leading space to prevent empty spaces in markup if the attribute is not added at runtime

```php
<!-- Two arguments, outputs attribute only if the first argument is truthy -->
<button type="submit"<?=$this->attrIf($form->errors, 'disabled')?>>
    Submit Form
</button>

<!-- Optional third argument is a value assigned to the attribute if the first argument is truthy -->
<div<?=$this->attrIf($page->show_chart, 'data-chart-json', $page->chart_json)></div>

<!-- Optional fourth argument is a value assigned to the attribute if the first argument is falsey -->
<div<?=$this->attrIf($page->show_chart, 'data-chart-json', $page->chart_json, '{}')></div>
```

## fetchIf

Extends the native Plates `fetch` method with an added conditional. Second conditional argument is any value or type checked for truthiness. If truthy, inserts the named template, otherwise no action is taken. If the conditional argument is a WireArray or WireArray derived object, the template will only render if it contains items.

Third argument is an optional data array passed to Plates `fetch` function

See also: [`insert`](#insert)

```php
<!-- Works with any value type -->
<?php $this->insertIf('components::bio', $page->bio_text, ['bio' => $page->bio_text]) ?>

<!-- Works with WireArray and WireArray derived objects -->
<?php $this->insertIf('components::image_gallery', $page->images, ['images' => $page->images]) ?>

<!-- Works without a third data argument -->
<?php $this->insertIf('components::your_template_here', $someTruthyValue) ?>
```

## if

Outputs value if first argument is truthy, weakly compared. Useful for a single line one comparison/value where the value being evaluated is never output to the page. Returns null if first argument is falsey. More complex cases should use native PHP language features, [`match`](#match), or [`matchTrue`](#matchTrue)

Arguments and values returned may be of any type.

```php
<!-- Will output 'your order is ready' if `$orderReady` is true, $orderReady is never output to the page -->
<p>Hello, <?=$this->if($orderReady, 'your order is ready')?></p>

<!-- Native PHP alternatives -->

<!-- If a third argument is being passed, consider a ternary -->
<p>Hello, <?=$orderReady ? 'your order is ready' : 'please complete checkout')?></p>

<!-- If the first argument may contain an output value, or may be empty, consider the elvis operator -->
<p>Hello, <?=$shippingStatus ?: 'your order is still being processed')?></p>

<!-- If the first argument is an output value that may be null or not present in an array, consider the null coalescing operator -->
<p>Hello, <?=$order['shippingStatus'] ?? 'your order is still being processed')?></p>

<!-- If the first argument may be an object or null but required a method call, consider the null safe operator -->
<p>Hello, <?=$order?->shippingMessage() ?? 'your order is still being processed')?></p>
```

## ifEq

Version of [if](#if) that outputs a single value if the first argument matches the second argument weakly compared. Returns null otherwise. Pass `true` as the fourth argument for strict comparison.

Arguments and values returned may be of any type

```php
<label>
    <span class="form-label">Name</span>
    <?=$this->ifEq($errors['name'], 'required', '<span class="form-label">This field is required</span>')?>
    <input type="text" class="required">
</label>

<!-- 4th argument true forces strict === comparison -->
<input type="text" value="<?=$this->ifEq($somethingNull, '0', 'Will not output', true)?>">
```

## ifPage

Checks if the current page matches the provided page. Optional second argument will be the value returned if the page passed matches the current page. Third argument is the value returned if the page is not a match. Returns a boolean by default.

```php
<ul>
  <?php foreach ($pages->get('/')->children->prepend($pages->get('/')) as $navPage): ?>
    <li>
      <a href="<?=$navPage->url?>" class="<?=$this->ifPage($navPage, 'active')?>">
        <?=$navPage->title?>
      </a>
    </li>
  <?php endforeach ?>
</ul>
```

## ifParam

Checks for a specified GET parameter for a specified value in the current URL. Omitting the expected value will check that the parameter exists.

```php
<?php if ($this->ifParam('foo', 'bar')): ?>
  <p>The parameter 'foo' is present in the current URL and has a value of 'bar'</p>
<?php endif ?>

<p>Is the parameter 'foo' present in the current URL with a value of 'bar'? <?=$this->ifParam('foo', 'bar', 'yes', 'no')?></p>

<?php if ($this->ifParam('foo')): ?>
  <p>The parameter 'foo' is present in the current URL</p>
<?php endif ?>

<?php if ($this->ifParam('foo', true)): ?>
  <p>The parameter 'foo' is present in the current URL</p>
<?php endif ?>

<?php if ($this->ifParam('foo', false)): ?>
  <p>The parameter 'foo' is not present in the current URL</p>
<?php endif ?>
```

## ifPath

Checks if the current page matches the provided page. Optional second argument will be the value returned if the page passed matches the current page. Third argument is the value returned if the page is not a match. Returns a boolean by default.

```php
<ul>
  <?php foreach ($listOfPages as $navPage): ?>
    <li>
      <a href="<?=$navPage->url?>" class="<?=$this->ifPage($navPage, 'active')?>">
        <?=$navPage->title?>
      </a>
    </li>
  <?php endforeach ?>
</ul>
```

## insertIf

Extends the native Plates `insert` method with an added conditional. Second argument is any value or type checked for truthiness. If truthy, inserts the named template, otherwise no action is taken. If the conditional argument is a WireArray or WireArray derived object, the template will only render if it contains items.

Third argument is an optional data array passed to Plates `insert` method.

See also: [`fetch`](#fetch)

```php
<!-- Works with any value type -->
<?php $this->insertIf('components::bio', $page->bio_text, ['bio' => $page->bio_text]) ?>

<!-- Works with WireArray and WireArray derived objects -->
<?php $this->insertIf('components::image_gallery', $page->images, ['images' => $page->images]) ?>

<!-- Works without a third data argument -->
<?php $this->insertIf('components::your_template_here', $someTruthyValue) ?>
```

## match

Outputs value in an array of cases where key matches the first argument passed. Optional third argument for default case

```php
<!-- Returns first value where key matches the first argument. Returns null if no cases match -->
<div class="<?=$this->match($color, ['red' => 'bg-red-500', 'yellow' => 'bg-amber-500', 'green' => 'bg-emerald-500'])?>">
    Hello!
</div>

<!-- With default 3rd argument -->
<div class="<?=$this->match($color, ['red' => 'bg-red-500', 'yellow' => 'bg-amber-500', 'green' => 'bg-emerald-500'], 'bg-blue-500')?>">
    Hello!
</div>
```

## matchTrue

Returns the key where the first value in an array is truthy

```php
<p>Your account is <?=$this->matchTrue(['current', $daysUntilDue >= 1, 'due' => $daysUntilDue == 0, 'past due' => $daysUntilDue < 0])?></p>
```

## switch

Alias for [`match`](#match)

## tagIf

Outputs one of two tags depending on the truthiness of the first argument with a method that will close with the tag that was opened

```php
<<?=$this->tagIf($page->headline, 'h3', 'h2'?> class="text-neutral-500">
    <?=$page->text?>
</<?=$this->ifTag()?>>

<!-- May optionally close with tagIf when no arguments are passed -->
<<?=$this->tagIf($page->headline, 'h3', 'h2'?> class="text-neutral-500">
    <?=$page->text?>
</<?=$this->tagIf()?>>
```