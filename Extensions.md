# Plates for ProcessWire Extensions

Plates for ProcessWire comes with custom extensions pre-built for use in your Plates template files. These extensions are optional and can be enabled when configuring the module. These extensions are purely provided as quality-of-life additions and are not necessary to use Plates in your ProcessWire project.

---

## Conditionals Extension

Helper methods that supplement the [alternate control structures](The "Syntax" page in the Plates documentation is just a few style recommendations and best practices that often apply to PHP in general. [You can view the not-really-a-syntax page here](https://platesphp.com/templates/syntax/).) provided by PHP that are useful when rendering markup. Many of these are influenced by [Latte Tags](https://latte.nette.org/en/tags).

### Switch

Outputs value in an array where key matches the first argument passed

```php
<div class="<?=$this->switch($page->color, ['red' => 'bg-red-500', 'yellow' => 'bg-amber-500', 'green' => 'bg-emerald-500'])?>">
    Hello!
</div>
```

### Conditional Tag

Outputs one of two tags depending on the truthiness of the first argument

```php
<<?=$this->tagIf($page->headline, 'h3', 'h2'?> class="text-neutral-500">
    <?=$page->text?>
</<?=$this->ifTag()?>>
```

### Conditional Attribute

Outputs an attribute if conditional is truthy. Second argument is the attribute. Optional third argument is the attribute value.

```php
<!-- Two arguments -->
<button type="submit" <?=$this->attrIf($form->errors, 'disabled')?>>
    Submit Form
</button>

<!-- Three arguments -->
<div <?=$this->attrIf($page->show_chart, 'data-chart-json', $page->chart_values)>
</div>
```

### Conditional Class Attribute

Shorthand for conditional attribute, but assumes class. Can be used with one or both values

```php
<button type="submit" <?=$this->classIf($form->errors, 'border-2 border-red-500', 'bg-blue-200')?>>
    Submit Form
</button>
```

---

## Functions Extension

The Functions Extension provides an selection of helper functions to make working with values easier. It is also built for ProcessWire specifically. Functions that accept arrays also accept WireArray and WireArray derived objects. While WireArray objects may have similar methods, these are chainable with `$this->batch()` and work whether WireArray or array is passed to make working with different object types easier.

The philosophy behind this extension is that, as with Plates itself, native PHP functions are preferred. Unless a signifivant improvement can be made or use case satisfied, there's not really a need to implements a replication. This extension won't implement a `capitalize()` function when `ucfirst()` already exists and can be batched. Additionally, this extension avoids implementing functions that take closures for arguments and instead focuses on formatting, conversion, casting, and sorting.

Many of these functions are influenced by [Latte Filters](https://latte.nette.org/en/filters). Any function that accepts one argument can be chained using the `$this->batch()` method call. If functions accept more than one argument but may still execute when only one argument is passed, it can still be batched. This applies to both standard PHP functions as well as any custom extension functions.

Most functions are null safe where possible and impressive batchable chains can be created using combinations of these functions and native PHP functions.

### batchArray

Extends Plates' `batch()` method to each item in an array

```php
<?php foreach ($this->batchArray(['hello THERE', ' <div>how are you?</div>'], 'stripHtml|strtolower|ucfirst') as $text): ?>
    <p><?=$text?></p>
<?php endforeach ?>

<!-- Process large numbers of field values easily -->
<?php foreach ($this->batchArray($activities->explode('summary'), 'stripHtml|ucfirst') as $text): ?>
    <p><?=$text?></p>
<?php endforeach ?>
```

### bit

Useful when `true` or `false` needs to be output in some way to the page where `true` and `false`boolean aren't output to the page via `echo`, or a value is truthy/falsey that would be better converted to a simple value. Very useful when used with Alpine JS.

**Batchable**

```php
<div x-data="{
       showMap: <?=$this->bit($page->show_map)?>,
       init() {
         if (this.showMap) {
            // ...
         }
       }
     "
>
    <div id="google-map"></div>
</div>
```

### clamp

Returns a number limited to and inclusive of the max and min values

```php
<php $this->clamp(523, 100, 500)?> // => 500
<php $this->clamp(125, 100, 500)?> // => 125
<php $this->clamp(82, 100, 500)?> // => 100
```

### csv

Converts an array of values to CSV string via a single argument function.

**Batchable**

```php
<=$this->csv('One fish', 'Two fish', 'Red fish', 'Blue fish')?>
<=$this->batch($this->csv('One fish', 'Two fish', 'Red fish', 'Blue fish'), 'strtolower|ucwords');
```

### difference

Subtracts all values in an array or WireArray by property. Accepts values that can be castable to numeric values, including integers and floats as strings. Null values and empty strings counted as 0. Accepts a WireArray if a property is provided as a field accessor.

**Batchable**
 
```php
<=$this->difference([300, 100, 20, 50])?> <!-- 130 -->
<=$this->difference($page->discounts, 'amount_field');
```

### divisibleBy

Returns whether a number can be divided by another 

```php
<ul class="grid gap-2 <?=$this->divisibleBy($page->books->count(), 3) ? 'grid-cols-3' : 'grid-cols-2'?>">
<?php foreach ($page->books as $book):?>
    <li>
        <p><?=$book->title?></p>
        <p><?=$book->author?></p>
    </li>
<?php endforeach?>
</ul>
```

### eq

Alias for [`nth`](#nth) that mirrors that WireArray `eq` method

### even

Returns whether a number is even or not

**Batchable**

```php
<div><?=$this->even($page->number) ? 'Even Steven' : 'Odd Man Out'?></div>
```

### falseToNull

Converts a value of `false` to `null`, all other values return original value

**Batchable**

```php
<div><?=$this->odd($page->number) ? "You're Odd" : 'Equal Slice'?></div>
```

### first

Returns the first item in an array, WireArray, or first character in a string, null safe

**Batchable**

```php
<!-- Works with arrays -->
<div>The first movie showing is: <?=$this->first(['10:00', '12:30', '15:45'])?>.</div>

<!-- Works with strings -->
<div>Your name starts with the letter <?=$this->first($page->person_name)?>.</div>

<!-- Works with WireArray and WireArray derived objects -->
<div>The winner of the race is: <?=$this->batch($page->race_results, 'first|strtolower|ucwords'?>.</div>
```

### group

Groups an array of objects by property or array of arrays by key, also works with WireArray and WireArray derived objects. Returns an array keyed by specified property/key

Results may be optionally key sorted by passing true, 'asc', or 'desc' as the third argument

```php
<!--
Assuming:
$people = [
    ['name' => 'Tom', 'age' => 28],
    ['name' => 'MARY', 'age' => 42],
    ['name' => 'Gary', 'age' => 28],
    ['name' => 'jerry', 'age' => 36],
];
-->
<?php foreach ($this->group($people, 'age') as $age => $persons): ?>
    <div>
        <p>Age: <?=$age?></p>
        <ul>
            <?php foreach ($persons as $person): ?>
                <li><?=$person['name']?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endforeach ?>

<!-- Pass true/'asc'/'desc' as the third argument to sort by group keys -->
<?php foreach ($this->group($people, 'age', 'asc') as $age => $persons): ?>
    <div>
        <p>Age: <?=$age?></p>
        <ul>
            <?php foreach ($persons as $person): ?>
                <li><?=$this->batch($person['name'], 'trim|strtolower|ucfirst')?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endforeach ?>

<!-- Group WireArray objects by property. If group was executed on a WireArray object, grouped items will be WireArrays -->
<?php foreach ($this->group($page->people, 'age', 'asc') as $age => $persons): ?>
    <div>
        <p>Age: <?=$age?></p>
        <ul>
            <?php foreach ($persons as $person): ?>
                <li><?=$this->batch($person->name, 'trim|strtolower|ucfirst')?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endforeach ?>
```


### odd

Returns whether a number is odd or not

**Batchable**

```php
<div><?=$this->odd($page->number) ? "You're Odd" : 'Equal Slice'?></div>
```

### length

Gets the length of an array, WireArray, or string, null safe

**Batchable**

```php
<!-- Works with arrays -->
<div>You have <?=$this->length(['book', 'hat', 'sunglasses'])?> items in your cart.</div>

<!-- Works with strings -->
<div>You have <?=$this->length($page->person_name)?> letters in your name.</div>

<!-- Works with WireArray and WireArray derived objects -->
<div>You have <?=$this->length($page->family_members)?> family members coming to dinner.</div>
```

#### Split

Splits a string to an array of characters, optionally specifying a separator, null safe

**Batchable**

```php
<!-- Splits characters by default -->
<?php foreach ($this->split('abc') as $letter): ?>
    <p>Letter: <?=$letter?></p>
<?php endforeach ?>

<!-- Splits characters with a defined separator -->
<?php foreach ($this->split('a|b|c', '|') as $letter): ?>
    <p>Letter: <?=$letter?></p>
<?php endforeach ?>
```

#### Reverse

Reverses strings, arrays, and WireArrays, null safe

**Batchable**

```php
<!-- Works with arrays -->
<div><?=$this->reverse([1, 39, 17, 26])?>.</div>

<!-- Works with strings -->
<div>Your name, but backwards <?=$this->reverse($page->person_name)?>.</div>

<!-- Works with WireArray and WireArray derived objects, useful for batching -->
<div>Last place: <?=$this->batch($page->contestants, 'reverse')->title?>.</div>
```

#### Random

Returns a random item in an array, WireArray, random character in a string, or random number in an integer

**Batchable**

```php
<!-- Works with arrays -->
<div>Your lucky number is: <?=$this->random([1, 39, 17, 26])?>.</div>

<!-- Works with strings -->
<div>Your name contains this letter <?=$this->random($page->person_name)?>.</div>

<!-- Works with WireArray and WireArray derived objects -->
<div>You win: <?=$this->batch($page->prizes, 'random')?>.</div>
```

#### Truncate

Truncates a string, wrapper for `WireTextTools::truncate()` method, accepts all arguments that ProcessWire method does

```php
<div>Summary: <?=$this->truncate($page->description, 500)?></div>
```

#### Last

Returns the last item in an array, WireArray, or last character in a string, null safe

**Batchable**

```php
<!-- Works with arrays -->
<div>The last number is: <?=$this->last([1, 2, 3, 4])?>.</div>

<!-- Works with strings -->
<div>The last letter of your name is <?=$this->last($page->person_name)?>.</div>
```

#### Slice

Slices a string, array, WireArray, or WireArray derived object

```php
<!-- Works with arrays -->
<?=$this->slice([1, 2, 3], 1, 2)?>

<!-- WireArrays -->
<?=$this->slice($page->books, 3)?>

<!-- Strings -->
<?=$this->slice($page->title, 2)?>
```

#### URL
Creates a query string or adds a query to a URL

```php
<a href="<?=$this->url($page->external_url, ['utm_source' => $page->title, 'utm_campaign' => $page->campaign])?>">Find out more</a>
```

### Asset Loader Extension

The asset loader extension provides tools to easily manage loading CSS, JS, and font assets. In the case of CSS and JS files,  cache parameter strings based on the last update time for files are automatically added when rendering to the page. It also provides tools for preloading assets as needed. With the asset loader extension your `<link>` and `<script>` tags are automatically created for you.

This extension provides two ways to interact with your files:

**Using Folder Definitions**

For a native feel that matches Plates' own [Folders](https://platesphp.com/engine/folders/) feature, you may set up folder definitions within the Plates for ProcessWire module config. To do this, enable the Asset Loader extension, then specify folder names and associated directories under "Asset Loader - Folder Definitions". Folder names and their locations are entirely your choice and directories may be located anywhere relative to the root directory. Example:

```
css::/site/assets/bundle/styles
styles::/site/resources/css
js::/site/templates/javascript
scripts::/site/js
lib::/lib
fonts::/site/fonts
someCompletelyRandomName::/any/path/you/want
```

**Using Relative Paths**

If folder definitions feel like a little too much black magic or you just prefer using file paths, this extension will work using that method as well. All of the features will work exactly the same way and as long as the file exists, they'll also be given a cache busting parameter as well. There are no downsides to using file paths over folder definitions.

#### Linking Assets

Linking assets is easy. If you are using folder definitions, the extension will automatically output the correct HTML tag. If you're not using folder definitions, just use the appropriate function. If the file is found on the filesystem, a cache busting parameter will be added, otherwise it will be left off.

**Stylesheets**

```php
<!-- With folder definitions -->
<?=$this->linkAsset('css::styles.css')?>

<!-- Without folder definitions -->
<?=$this->linkCss('/path/to/your/styles.css')?>

<!-- Output -->
<link href="/path/to/your/styles.css?v=1734630086" rel="stylesheet">

<!-- You may also pass an arbitrary amount of attributes as a second array argument using either rendering method -->
<?=$this->linkAsset('css::styles.css', ['id' => 'some-id', 'data-some-attribute'])?>

<!-- Output -->
<link id="some-id" data-some-attribute href="/site/assets/bundle/styles/app.css?v=1734630086" rel="stylesheet">
```

**JavaScript**

```php
<!-- With folder definitions -->
<?=$this->linkAsset('js::script.js')?>

<!-- Without folder definitions -->
<?=$this->linkJs('/path/to/your/script.js')?>

<!-- Output: -->
<script src="/path/to/your/script.js?v=1734630080"></script>

<!-- Also takes optional second argument array of attributes -->
<?=$this->linkJs('/path/to/your/script.js', ['id' => 'some-id', 'data-some-attribute'])?>

<!-- Output -->
<script id="some-id" data-some-attribute src="/path/to/your/script.js?v=1734630080"></script>
```

#### Inlining Assets

You can also inline the contents of CSS or JS assets

**Stylesheets**

```php
<!-- With folder definitions -->
<?=$this->inlineAsset('css::styles.css')?>

<!-- Without folder definitions -->
<?=$this->inlineJs('/path/to/your/styles.css')?>

<!-- Output: -->
<style>
  body {
    font-family: 'Helvetica';
  }
</style>

<!-- Also takes optional second argument array of attributes, works with either method -->
<?=$this->inlineAsset('css::styles.css', ['id' => 'some-id', 'data-some-attribute'])?>

<!-- Output -->
<style id="some-id" data-some-attribute>
  body {
    font-family: 'Helvetica';
  }
</style>
```

**JavaScript**

```php
<!-- With folder definitions -->
<?=$this->inlineAsset('js::script.js')?>

<!-- Without folder definitions -->
<?=$this->inlineJs('/path/to/your/script.js')?>

<!-- Output: -->
<script>
  console.log('Hello');
</script>

<!-- Also takes optional second argument array of attributes, works with either method -->
<?=$this->inlineAsset('js::script.js', ['id' => 'some-id', 'data-some-attribute'])?>

<!-- Output -->
<script id="some-id" data-some-attribute>
  console.log('Hello');
</script>
```

#### Preloading Assets

You can also preload assets. Preloading assets works with CSS, JS, and font files. CSS and JS will have the correct cache busting parameter appended to the URL

```php
<!-- You may pass any type of file when using configured folders -->
<?=$this->preloadAsset('css::styles.css')?>
<?=$this->preloadAsset('js::script.js')?>
<?=$this->preloadAsset('fonts::your-font.woff')?>

<!-- Withoud configured folders, call the respective methods -->
<?=$this->preloadCss('/path/to/your/styles.css')?>
<?=$this->preloadJs('/path/to/your/script.js')?>
<?=$this->preloadFont('/path/to/your-font.woff')?>

<!-- Output respectively -->
<link rel="preload" href="/path/to/your/styles.css?v=1734630086" as="style">
<link rel="preload" href="/path/to/your/script.js?v=1734630080" as="script">
<link rel="preload" href="/path/to/your-font.woff" as="font" crossorigin>
```

#### Asset Loader Debug Mode

Enabling the asset loader extension will provide a method to enable a debug mode exclusively for this extension. This may be useful for troubleshooting during development, but is not recommended for use in production. Enabling debug mode will cause exceptions to be thrown if attemping to load/inline/preload a file that does not exist or attempt to use a configured folder that has not been set up on the module config page.

### Wire Extension

The Wire Extension provides easy access to ProcessWire utilities that would otherwise need to be instantiated. All objects are memoized except for `wireArray()` which returns a new instance each time

```php
<!-- Get an instance of WireRandom -->

<div>Here is a random string <?=$this->wireRandom()->alphanumeric()?></div>

<!-- Get an instace of WireTextTools -->

<p><?=$this->wireTextTools()->truncate($page->text_field, 250)?></p>

<!-- Create a new WireArray instance -->

<p>Buildings taller than 50 feet:</p>
<?php foreach ($this->wireArray($somePage, $anotherPage, $lastPage)->filter('height>=50') as $building): ?>
  <p><?=$building->title?></p>
<?php endforeach ?>
```
