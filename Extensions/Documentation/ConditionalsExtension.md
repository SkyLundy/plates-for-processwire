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

## attrIfPage

Checks whether a Page object, selector, or page ID passed as the first argument matches the current page.

Attributes are always rendered with a leading space.

If only an attribute is passed, the attribute will be returned if page matches current page, null otherwise
If only an attribute and a page match attribute value are passed, the attribute and value will only render if page matches
If both page match/mismatch values are passed, the attribute will always be rendered with the value according to page match

See also: [`attrIf`](#attrif)
See also: [`ifPage`](#ifPage)

```php
<!-- Adds data-currentPage if first argument matches current page -->
<ul>
  <?php foreach ($pageArray as $thisPage): ?>
    <li<?=$this->attrIfPage($thisPage, 'data-current-page')>
      <a href='<?=$thisPage->url?>'><?=$thisPage->title?></a>
    </li>
  <?php endforeach ?>
</ul>

<!-- Adds class="active" if first argument matches current page -->
<ul>
  <?php foreach ($pageArray as $thisPage): ?>
    <li<?=$this->attrIfPage($thisPage, 'class', 'active')>
      <a href='<?=$thisPage->url?>'><?=$thisPage->title?></a>
    </li>
  <?php endforeach ?>
</ul>

<!-- Adds class="active" if first page matches current page, class="inactive" if page does not match -->
<ul>
  <?php foreach ($pageArray as $thisPage): ?>
    <li<?=$this->attrIfPage($thisPage, 'class', 'active', 'inactive')>
      <a href='<?=$thisPage->url?>'><?=$thisPage->title?></a>
    </li>
  <?php endforeach ?>
</ul>

<!-- May also use a selector or page ID -->
<span<?=$attrIfPage('/', 'data-current')?>>Home</span>
```

## attrIfNotPage

Inverse of [`attrIfPage`](#attrifpage)


## fetchIf

Extends the native Plates `fetch` method with an added conditional. Second conditional argument is any value or type checked for truthiness. If truthy, inserts the named template, otherwise no action is taken. If the conditional argument is a WireArray or WireArray derived object, the template will only render if it contains items.

Third argument is an optional data array passed to Plates `fetch` function

See also: [`insertIf`](#insertif)

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

Version of [if](#if) that outputs a single value if the first argument matches the second argument strictly compared. Returns null otherwise. Pass `false` as the fourth argument for weak comparison.

Arguments and values returned may be of any type

```php
<label>
    <span class="form-label">Name</span>
    <?=$this->ifEq($errors['name'], 'required', '<span class="form-label">This field is required</span>')?>
    <input type="text" class="required">
</label>

<!-- 4th argument true forces weak == comparison -->
<?=$this->ifEq($somethingNull, '0', 'Close enough', false)?>
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

Checks for a specified GET parameter, and optionally for a specified value in the current URL. Omitting an expected value will check that the parameter exists regardless of value.

```php
<!-- https://somewebsite.com/?foo=bar returns true -->
<?php if ($this->ifParam('foo', 'bar')): ?>
  <p>The parameter 'foo' is present in the current URL and has a value of 'bar'</p>
<?php endif ?>

<!-- https://somewebsite.com/?foo=bar returns 'yes' -->
<p>Is the parameter 'foo' present in the current URL with a value of 'bar'? <?=$this->ifParam('foo', 'bar', 'yes', 'no')?></p>

<!-- https://somewebsite.com/?foo=foobar returns 'no' -->
<p>Is the parameter 'foo' present in the current URL with a value of 'bar'? <?=$this->ifParam('foo', 'bar', 'yes', 'no')?></p>

<!-- https://somewebsite.com/?foo=foobar returns 'true' -->
<?php if ($this->ifParam('foo')): ?>
  <p>The parameter 'foo' is present in the current URL</p>
<?php endif ?>

<!-- https://somewebsite.com/?foo=foobar - Passing second value as true checks if parameter exists regardless of value and returns the third parameter -->
<?=$this->ifParam('foo', true, "<p>The parameter 'foo' is present in the current URL</p>")?>

<!-- https://somewebsite.com/?fizz=buzz - Passing second value as true checks if parameter exists regardless of value -->
<?php if ($this->ifParam('foo', false)): ?>
  <p>The parameter 'foo' is not present in the current URL</p>
<?php endif ?>
```

## ifPath

Checks if the current URL path matches the provided URL path. Optional second argument will be the value returned if the page passed matches the current page. Optional third argument is the value returned if the page is not a match. Returns a boolean if only one argument is passed.

Ignores leading/trailing slashes, ignores GET parameters

```php
<!-- https://somewebsite.com/  Returns true -->
<?php if ($this->ifPath('/')): ?>
  <p>Welcome home!</p>
<?php endif ?>

<!-- https://somewebsite.com/somewwhere/else Returns 'Goodbye...' -->
<p><?=$this->ifPath('/hello', 'Hello!', 'Goodbye...')?></p>
```

## ifUrl

Checks if the current URL matches the provided URL. Optional second argument will be the value returned if the URL matches the current URL. Optional third argument is the value returned if the page is not a match. Returns a boolean if only one argument is passed.

Ignores leading/trailing slashes, ignores GET parameters. URL protocol must match, subdomains must match.

```php
<!-- https://somewebsite.com/  Returns true -->
<?php if ($this->ifUrl('https://somewebsite.com/')): ?>
  <p>Welcome home!</p>
<?php endif ?>

<!-- https://somewebsite.com/somewwhere/else Returns 'Not a match' -->
<p><?=$this->ifUrl('https://somewebsite.com/somewwhere/else', 'Match!', 'Not a match')?></p>

<!-- https://somewebsite.com/hello Protocol doesn't match, returns 'Oops' -->
<p><?=$this->ifPath('http://somewebsite.com/hello', 'All good!', 'Oops')?></p>

<!-- https://somewebsite.com Protocol doesn't match, returns 'Whoa there, wrong subdomain' -->
<p><?=$this->ifPath('https://look.somewebsite.com', 'Winner!', 'Whoa there, wrong subdomain')?></p>
```


## ifPath

Checks if the current URL path matches the provided URL path. Optional second argument will be the value returned if the page passed matches the current page. Optional third argument is the value returned if the page is not a match. Returns a boolean if only one argument is passed.

Ignores leading/trailing slashes, ignores GET parameters

```php
<!-- https://somewebsite.com/  Returns true -->
<?php if ($this->ifPath('/')): ?>
  <p>Welcome home!</p>
<?php endif ?>

<!-- https://somewebsite.com/somewwhere/else Returns 'Not home yet...' -->
<p><?=$this->ifPath('/', 'Welcome home!', 'Not home yet...')?></p>
```

## insertIf

Extends the native Plates `insert` method with an added conditional. Second argument is any value or type checked for truthiness. If truthy, inserts the named template, otherwise no action is taken. If the conditional argument is a WireArray or WireArray derived object, the template will only render if it contains items.

Third argument is an optional data array passed to Plates `insert` method.

See also: [`fetchIf`](#fetchif)

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

<!-- May optionally close with tagIf -->
<<?=$this->tagIf($page->headline, 'h3', 'h2'?> class="text-neutral-500">
    <?=$page->text?>
</<?=$this->tagIf()?>>
```

## wrapIf

Wraps a value in a given tag if the conditional is truthy, optional fallback tag may be provided. If conditional is falsey and no fallback tag is provided, the value is returned without additional markup.

The tag provided must be closable and may contain any attributes desired. Tags provided must be proper syntax with opening and closing angle brackets.

```php
<!-- Creates a link for every page that is not the current page -->
<ul>
  <?php foreach ($pageArray as $thisPage): ?>
    <li>
      <?=$this->wrapIf($page->id === $thisPage->id, $thisPage->title, "<a href='{$thisPage->url}'>")?>
    </li>
  <?php endforeach ?>
</ul>

<!-- Creates a link for every page that is not the current page, the current page is wrapped in a <span>, combine with ifPage() -->
<ul>
  <?php foreach ($pageArray as $thisPage): ?>
    <li>
      <?=$this->wrapIf(
        !$this->ifPage($thisPage),
        $thisPage->title,
        "<a href='{$thisPage->url}'>",
        "<span class='current'>"
    )?>
    </li>
  <?php endforeach ?>
</ul>
```