# Plates for ProcessWire Extensions

Plates for ProcessWire comes with custom extensions pre-built for use in your Plates template files. These extensions are optional and can be enabled when configuring the module. These extensions are purely provided as quality-of-life additions and are not necessary to use Plates in your ProcessWire project.

If bugs are encountered, please open an issue on the Plates for ProcessWire Github repository. If you're able to provide a bugfix, merge requests are very much welcome.

---

## Conditionals Extension

Helper methods that supplement the [alternate control structures](The "Syntax" page in the Plates documentation is just a few style recommendations and best practices that often apply to PHP in general. [You can view the not-really-a-syntax page here](https://platesphp.com/templates/syntax/).) provided by PHP useful when rendering markup. 

### attrIf

Outputs an attribute if conditional is truthy. Second argument is the attribute. Optional third argument is the attribute value.

Note that the space before the opening `<?=` is ommitted. Attributes are automatically padded with a leading space to prevent empty spaces in markup if the attribute is not added at runtime

```php
<!-- Two arguments -->
<button type="submit"<?=$this->attrIf($form->errors, 'disabled')?>>
    Submit Form
</button>

<!-- Three arguments -->
<div<?=$this->attrIf($page->show_chart, 'data-chart-json', $page->chart_values)>
</div>
```

### classIf

Shorthand for conditional attribute, but assumes class. Can be used with one or both values

Note that the space before the opening `<?=` is ommitted. Attributes are automatically padded with a leading space to prevent empty spaces in markup if the attribute is not added at runtime

```php
<button type="submit"<?=$this->classIf($errors, 'border-2 border-red-500', 'bg-blue-200')?>>
    Submit Form
</button>
```

### if

Outputs value if first argument is truthy. Useful for a single line one comparison/value where the value being evaluated is never output to the page. Optional second value to return on false. Weakly compares values. More complex cases should use native PHP language features, [`match`](#match), or [`matchTrue`](#matchTrue)

Arguments and values returned may be of any type.

```php
<!-- Will output 'your order is ready' if `$orderReady` is true, $orderReady is never output to the page -->
<p>Hello, <?=$this->if($orderReady, 'your order is ready')?></p>

<!-- With optional third argument -->
<p>Hello, <?=$this->if($orderReady, 'your order is ready', 'please complete checkout')?></p>

<!-- Native PHP alternatives -->

<!-- If a third argument is being passed, consider a ternary -->
<p>Hello, <?=$orderReady ? 'your order is ready' : 'please complete checkout')?></p>

<!-- If the first argument may contain an output value, or may be empty, consider the elvis operator -->
<p>Hello, <?=$shippingStatus ?: 'your order is still being processed')?></p>

<!-- If the first argument is an output value that may be null or not present in an array, consider the null coalescing operator -->
<p>Hello, <?=$order['shippingStatus'] ?? 'your order is still being processed')?></p>

<!-- If the first argument may be an object or null, consider the null safe operator -->
<p>Hello, <?=$order?->shippingStatus ?? 'your order is still being processed')?></p>

<!-- If the first argument may be an object or null but required a method call, consider the null safe operator -->
<p>Hello, <?=$order?->shippingMessage() ?? 'your order is still being processed')?></p>
```

### ifEq

Outputs value if first argument is true compared to second argument. Optional fourth boolean argument for strict/weak comparison. Default is strict.

Arguments and values returned may be of any type

```php
<label>
    <span class="form-label">Name</span>
    <?=$this->ifEq($errors['name'], 'required', '<span class="form-label">This field is required</span>')?>
    <input type="text" class="required">
</label>
```

### match

Outputs value in an array of cases where key matches the first argument passed. Optional third argument for default case

```php
<div class="<?=$this->match($color, ['red' => 'bg-red-500', 'yellow' => 'bg-amber-500', 'green' => 'bg-emerald-500'])?>">
    Hello!
</div>

<!-- With default -->
<div class="<?=$this->match($color, ['red' => 'bg-red-500', 'yellow' => 'bg-amber-500', 'green' => 'bg-emerald-500'], 'bg-blue-500')?>">
    Hello!
</div>
```

### matchTrue

Returns a value based on a provided evaluation. Similar to match but can handle more complex cases.

```php
<p>Your account is <?=$this->matchTrue(['current', $daysUntilDue >= 1, 'due' => $daysUntilDue == 0, 'past due' => $daysUntilDue < 0])?></p>
```

### switch

Alias for [`match`](#match )

### tagIf

Outputs one of two tags depending on the truthiness of the first argument

```php
<<?=$this->tagIf($page->headline, 'h3', 'h2'?> class="text-neutral-500">
    <?=$page->text?>
</<?=$this->ifTag()?>>

<!-- May optionally close with tagIf when no arguments are passed -->
<<?=$this->tagIf($page->headline, 'h3', 'h2'?> class="text-neutral-500">
    <?=$page->text?>
</<?=$this->tagIf()?>>
```

---

## Functions Extension

The Functions Extension provides a selection of helper functions to make working with values in templates easier. It is also built for ProcessWire specifically. Functions that accept arrays generally accept WireArray and WireArray derived objects. Where WireArray objects may have similar methods, these are chainable with `$this->batch()` and work whether WireArray or array is passed to make working with different object types easier and more uniform.

The philosophy behind this extension is that, as with Plates itself, native PHP functions are preferred. Unless a signifivant improvement can be made or use case satisfied, there's not really a need to implement a replication. This extension won't implement a `capitalize()` function when `ucfirst()` already exists and can be batched. Additionally, this extension does not implement functions that take closures as arguments and instead focuses on formatting, conversion, casting, sorting, and working with data structures in template context.

Many of these functions are influenced by [Latte Filters](https://latte.nette.org/en/filters). Any function that accepts one argument can be chained using the `$this->batch()` method call. If a function accepts more than one argument but additional arguments are optional, it can still be batched. This applies to both standard PHP functions as well as any custom extension functions.

Most functions are null safe where possible and impressive batchable chains can be created using combinations of these functions and native PHP functions.

### batchArray

Alias for [`batchEach`](#batcheach)

### batchEach

Extends Plates' `batch()` method to each item in an array

```php
<?php foreach ($this->batchEach(['hello THERE', ' <div>how are you?</div>'], 'stripHtml|trim|strtolower|ucfirst') as $text): ?>
    <p><?=$text?></p>
<?php endforeach ?>

<!-- Process large numbers of field values easily -->
<ul>
  <?php foreach ($this->batchEach($movies->explode('title'), 'stripHtml|trim|strtolower|ucwords') as $title): ?>
    <li><?=$title?></li>
  <?php endforeach ?>
</ul>
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

Alias for [`nth`](#nth)

### even

Returns whether a number is even or not

**Batchable**

```php
<p><?=$this->even($page->number) ? 'Even Steven' : 'Odd Man Out'?></p>
```

### falseToNull

Converts a value of `false` to `null`, all other values return original value

**Batchable**

```php
<div><?=$this->falseToNull($page->has_a_pet)?></div>
```

### filterNull

Removes all instances of null from an array or WireNull objects from WireArray and WireArray derived objects. Returns a new array or WireArray instance. Useful for reducing the need for secondary checking for `id` before outputting when working with WireArrays.

**Batchable**

```php
<ul>
  <?php foreach ($this->filterWireNull($page->pets) as $pet): ?>
    <li>
      <?=$pet->name?>
    </li>
  <?php endforeach ?>
</ul>

```

### first

Returns the first item in an array, WireArray, first character in a string, or first number in an integer, null safe.

Optional second boolean argument will remove all null or WireNull items from the array before getting the first item.

Alias for `nth` function index at 0. See [`nth`](#nth) for type handling and examples of working with nth values

**Batchable**

```php
<!-- Works with arrays -->
<p>The first movie showing is: <?=$this->first(['10:00', '12:30', '15:45'])?>.</p>

<!-- Works with strings -->
<p>Your name starts with the letter <?=$this->batch($this->first($page->person_name), 'strtoupper')?>.</p>

<!-- Works with WireArray and WireArray derived objects -->
<p>True first item, nulls removed: <?=$this->first($page->race_results, true)->title?>.</p>
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
    <p>Age: <?= $age ?></p>
    <ul>
      <?php foreach ($persons as $person): ?>
        <li><?= $person['name'] ?></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endforeach ?>

<!-- Pass true/'asc'/'desc' as the third argument to sort by group keys -->
<?php foreach ($this->group($people, 'age', 'asc') as $age => $persons): ?>
  <div>
    <p>Age: <?= $age ?></p>
    <ul>
      <?php foreach ($persons as $person): ?>
        <li><?= $this->batch($person['name'], 'trim|strtolower|ucfirst') ?></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endforeach ?>

<!-- Group WireArray objects by property. If group was executed on a WireArray object, grouped items will be WireArrays -->
<?php foreach ($this->group($page->people, 'age', 'asc') as $age => $persons): ?>
  <div>
    <p>Age: <?= $age ?></p>
    <ul>
      <?php foreach ($persons as $person): ?>
        <li><?= $this->batch($person->name, 'trim|strtolower|ucfirst') ?></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endforeach ?>
```

### isWireArray

Primarily used internally by the extension but provided in case it's useful. Identifies all WireArray and WireArray derived objects

```php
<?php if ($this->isWireArray($page->image_gallery)): ?>
  <ul>
    <?php foreach ($page->image_gallery as $image): ?>
      <li>
        <img src="<?=$image->url?>" alt="<?=$image->description?>">
      </li>  
    <?php endforeach ?>
  </ul>
<?php endif ?>
```

### jsonDecodeArray

Makes decoding to an array a batchable by eliminating the second boolean argument of PHP's `json_decode()` function while retaining transparency for all other function parameters. 

**Batchable**

```php
<ul>
  <?php foreach ($this->jsonDecodeArray($libraryApiResponse) as $author => $books): ?>
    <li>
      <h2><?=$author?></h2>
      <ul>
        <?php foreach ($books as $book): ?>
          <li><?=$book->title?></li>
        <?php endforeach ?>
      </ul>
    </li>  
  <?php endforeach ?>
</ul>
```

### last

Returns the last item in an array, WireArray, or last character in a string, null safe

Optional second boolean argument will remove all null or WireNull items from the array before getting the first item.

Alias for `nth` function retrieving last item by index. See [`nth`](#nth) for type handling and examples of working with nth values

**Batchable**

```php
<!-- Works with arrays -->
<p>The last movie showing is: <?=$this->first(['10:00', '12:30', '15:45'])?>.</p>

<!-- Works with strings -->
<p>Your name ends with the letter <?=$this->first($page->person_name)?>.</p>

<!-- Works with WireArray and WireArray derived objects -->
<p>True last item, nulls removed: <?=$this->last($page->race_results, true)->title?>.</p>
```

### length

Gets the length of an array, WireArray, or string, null safe

**Batchable**

```php
<!-- Works with arrays -->
<p>You have <?=$this->length(['book', 'hat', 'sunglasses'])?> items in your cart.</p>

<!-- Works with strings -->
<p>You have <?=$this->length($page->person_name)?> letters in your name.</p>

<!-- Works with WireArray and WireArray derived objects -->
<p>You have <?=$this->length($page->family_members)?> family members coming to dinner.</p>
```

### merge

Merges an arbitrary number of arrays or WireArray objects into one. Must all be of the same type.

**Batchable**

```php
<!-- Works with arrays -->
<p>You have <?=$this->length(['book', 'hat', 'sunglasses'])?> items in your cart.</p>

<!-- Works with strings -->
<p>You have <?=$this->length($page->person_name)?> letters in your name.</p>

<!-- Works with WireArray and WireArray derived objects -->
<p>You have <?=$this->length($page->family_members)?> family members coming to dinner.</p>
```

### nth

Gets the value at the nth position of an array, string, integer, float, or WireArray,

Return type matches input type.

```php
<!-- Works with arrays, returns '12:30' -->
<p>The first movie showing is: <?=$this->nth(['10:00', '12:30', '15:45'], 1)?>.</p>

<!-- Nulls removed returns 15:45 -->
<p>The second movie showing is: <?=$this->nth(['10:00', null, '15:45'], 1, true)?>.</p>

<!-- Works with strings -->
<p>The second letter in your name is: <?=$this->batch($this->nth($page->person_name, 1), 'strtoupper')?>.</p>

<!-- Works with integers, returns integer 9 -->
<p>Lucky number: <?=$this->batch($this->nth(8392194, 2), 'strtoupper')?>.</p>

<!-- Integers passed as strings will return a string, returns '1' -->
<p>Lucky number: <?=$this->batch($this->nth('8392194', 4), 'strtoupper')?>.</p>

<!-- Works with floats, returns integer 4 -->
<p>Lucky number: <?=$this->batch($this->nth(3.14159, 3), 'strtoupper')?>.</p>

<!-- Will return the decimal if at nth, returns string '.' -->
<p>Lucky number: <?=$this->batch($this->nth(3.14159, 1), 'strtoupper')?>.</p>

<!-- Works with WireArray and WireArray derived objects, nulls removed with third true argument -->
<p>True 4th item, nulls removed: <?=$this->nth($page->race_results, 3, true)->title?>.</p>
```

### nthEnd

Gets the item at the nth location from the end of an array, string, integer, float, or WireArray

See [`nth`](#nth) for type handling and examples of working with nth indexes

### nth1

Gets the nth item in an array, string, integer, float, or WireArray from index beginning at 1 rather than 0

See [`nth`](#nth) for type handling and examples of working with nth indexes

### nth1End

Gets the item at the nth location from the end of an array, string, integer, float, or WireArray indexed from 1 rather than 0

See [`nth`](#nth) for type handling and examples of working with nth indexes

### odd

Returns whether an integer is odd or not

**Batchable**

```php
<p><?=$this->odd($page->number) ? "You're Odd" : 'Equal Slice'?></p>
```

### product

Multiplies all values in a list array, values by key in an associative array, values in an array of stdClass objects by property, or values in WireArray/WireArray derived object by property

**Batchable**

```php
<!-- Gets product of values in an array, outputs 6000 -->
<p>The product of the numbers in this series is: <?=$this->product([10, 20, 30])?></p>

<!-- Gets product of values in an associative array, outputs 6000 -->
<p>The product of the numbers in this series by 'foo' is: <?=$this->product([['foo' => 10], ['foo' => 20], ['foo' => 30]], 'foo')?></p>

<!-- Gets product of values in an associative array, outputs 6000 -->
<p>The product of the numbers in this series by 'foo' is: <?=$this->product($pages->find('field>=10'), 'foo')?></p>
```

### random

Returns a random item in an array, WireArray, random character in a string, random number in an integer, or random value in a float.

Does not generate cryptographically secure values

Uses [`nth`](#nth) for selecting at a randomized index, refer to [`nth`](#nth) for type handling

**Batchable**

```php
<!-- Works with arrays -->
<p>Your lucky number is: <?=$this->random([1, 39, 17, 26])?>.</p>

<!-- Works with strings -->
<p>Your name contains this letter <?=$this->random($page->person_name)?>.</p>

<!-- Works with WireArray and WireArray derived objects -->
<p>You win: <?=$this->batch($page->prizes, 'random')?>.</p>
```

### reverse

Reverses strings, integers, floats, arrays, and WireArrays, null safe

Return type will match input type. Integers return integers, floats return floats.

**Batchable**

```php
<!-- Works with arrays -->
<p><?=$this->reverse([1, 39, 17, 26])?>.</p>

<!-- Works with strings -->
<p>Your name, but backwards <?=$this->reverse($page->person_name)?>.</p>

<!-- Works with WireArray and WireArray derived objects, useful for batching -->
<p>Last place: <?=$this->batch($page->contestants, 'reverse')->title?>.</p>
```

### singleSpaced

Converts all instances of multiple spaces in strings to single spaces.

**Batchable**

```php
<!-- Works with arrays, returns 'who wrote this sentence?' -->
<p><?=$this->collapseSpaces('who   wrote  this  sentence?')?>.</p>

<!-- Batchable, outputs: 'Who wrote this sentence?' -->
<p>This sentence has been cleaned up <?=$this->batch('   who WROTE this   sentence? ', 'trim|singleSpaced|strtolower|ucfirst')?></p>
```

### slice

Slices a string, array, integer, float, WireArray, or WireArray derived object

Note: when slicing integers and floats, the return value is a string. This is an intentional decision to preserve leading zeroes should they exist.

```php
<!-- Works with arrays -->
<?=$this->slice([1, 2, 3], 1, 2)?>

<!-- WireArrays -->
<?=$this->slice($page->books, 3)?>

<!-- Strings -->
<?=$this->slice($page->title, 2)?>

<!-- Integers, returns '0003' -->
<?=$this->slice($this->slice(200003, 2)?>

<!-- If an integer or float is desired, consider casting or adding a PHP function to your batch chain -->
<?=(int) $this->slice(200003, 2)?>
                
<?=$this->batch($this->slice(200003, 2), 'intval')?>
```

### stripHtml

Removes HTML markup from a string or array of strings. Non-string values are returned as passed. Multidimensional arrays are processed recursively.

This is a wrapper for ProcessWire's `WireTextTools::markupToText()`. Second parameter takes an options array argument passed to the ProcessWire method

**Batchable**

```php
<!-- Strings -->
<?=$this->stripHtml("<blockquote><p>If I can't dance to it, it's not my revolution.</p></blockquote><p> —Emma Goldman</p>")?>

<!-- Arrays -->
<?php $this->stripHtml([
  "<span>You're traveling through another dimension --</span><br>",
  "<span>a dimension not only of sight and sound but of mind.</span><br>",
  "<span>A journey into a wondrous land whose boundaries are that of imagination.</span><br>",
  "<span>That's a signpost up ahead: your next stop: the Twilight Zone!</span><br>",
])?>

<!-- With recursive added to options argument -->
<?php $this->stripHtml([
  ['<p>One fish</p>', '<p>Two  fish</p>'],
  ['<p>Red   fish</p>', '<p>Blue fish</p>'],
], ['collapseSpaces' => true, 'recursive' => true])?>

<!-- Example using a WireArray with fields -->
<ul>
  <?php foreach ($this->stripHtml($page->space_facts->explode('fact_body'))) as $spaceFact): ?>
    <li><p><?=$spaceFact?></p></li>  
  <?php endforeach ?>
</ul>
```

### sum

Returns the sum of all numbers in an array, associative array, WireArray, or WireArray derived object

**Batchable**

```php
<!-- Gets sum of values in a list array, outputs 53 -->
<div>Total cups of coffee consumed last week: <?=$this->sum([1, 4, 9, 2, 3, 10, 24])?></div>

<!-- Gets sum of values in an associative array or array of objects, outputs 6 -->
 Cocktails consumed at Goldeneye reunion: <?=$this->sum([
  ['name' => 'James', 'drinks' => 2],
  ['name' => 'Natalia', 'drinks' => 3],
  ['name' => 'Boris', 'drinks' => 1],
], 'beers')?>
    
<!-- Gets sum of values from a WireArray -->
<p>Total points in high scores: <?=$this->product($pages->find('template=high_scores,points>=10'), 'points')?></p>
```

### toObject

Provides fluent access to nested values by converting all associative arrays to stdClass objects recursively. List arrays are not modified. Changes the method of accessing values from array `[]` notation to a fluent object `->` notation. Safe to use with arrays containing any data types, does not rely on `json_encode`/`json_decode`

Largely aesthetic, but may provide more readability for complex data structures

**Batchable**

```php
<php
// Given this array data structure
$people = [
    [
        'name' => 'Marty McFly',
        'occupation' => 'Time Traveler',
        'skills' => [
            'guitar',
            'skateboarding',
            'hoverboarding',
            ],
        'network' => [
            'family' => [
                [
                    'name' => 'Lorraine McFly',
                    'relation' => 'mother',
                ],
                [
                    'name' => 'George McFly',
                    'relation' => 'father',
                ],
            ],
            'connections' => [
                [
                    'name' => 'Doc Brown',
                    'relation' => 'friend',
                ],
                [
                    'name' => 'Jennifer Parker',
                    'relation' => 'girlfriend',
                ],
            ],
        ],
    ],
    // ... ommitted for brevity
];
?>

<!-- Object with fluent object access -->
<?php foreach ($this->toObject($people) as $person): ?>
  <h2><?=$person->name?></h2>
  <h3><?=$person->occupation?></h3>
  <p>Skills: <?=$this->csv($person->skills)?></p>
  <h3>Family</h3>
  <ul>
    <?php foreach ($person->network->family as $individual): ?>
      <li><?=$individual->name?>, <?=$individual->relation?></li>
    <?php endforeach ?>
  </ul>
  <h3>Connections</h3>
  <ul>
    <?php foreach ($person->network->connections as $individual): ?>
      <li><?=$individual->name?>, <?=$individual->relation?></li>
    <?php endforeach ?>
  </ul>
<?php endforeach ?>
```

### truncate

Truncates a string or an array of strings, wrapper for `WireTextTools::truncate()` method, accepts all arguments that ProcessWire method does

```php
<!-- Basic usage -->
<p>Summary: <?=$this->truncate($page->description, 500)?></p>

<!-- With arguments passed to WireTextTools::truncate() method -->
<p>Summary: <?=$this->truncate($page->description, 500, ['type' => 'sentence'])?></p>

<div>
	Famous grocery lists:
    <?php foreach ($this->truncate($page->grocery_lists->explode('text'), 500) as $text): ?>
      <p><?=$text?></p>
    <?php endforeach ?>
</div>
```

### unique

Returns only unique instances of values in a string, array, int, float, WireArray, or WireArray derived objects. The `WireArray::unique()` method is used when WireArray and WireArray derived objects are passed.

**Batchable**

```php
<!-- Strings, returns 'abc' -->
<p>Unique letters: <?=$this->unique('aabbcc')?></p>

<!-- Unique numbers, returns 3852 -->
<p>Unique letters: <?=$this->unique(33885555552)?></p>
    
<!-- With arguments passed to WireTextTools::truncate() method -->
<p>Summary: <?=$this->truncate($page->description, 500, ['type' => 'sentence'])?></p>
```

### url
Creates a query string or adds a query to a URL

```php
<a href="<?=$this->url($page->external_url, ['utm_source' => $page->title, 'utm_campaign' => $page->campaign])?>">Find out more</a>
```

### wireGetArray

An enhancement of the `WireArray::getArray()` method that adds optional recursion. Used internally by the extension.

**Batchable**

```php
<!-- Strings -->
<p>Unique letters: <?=$this->unique('aabbcc')?></p>

<!-- Unique numbers, returns 3852 -->
<p>Unique letters: <?=$this->unique(33885555552)?></p>
    
<!-- With arguments passed to WireTextTools::truncate() method -->
<p>Summary: <?=$this->truncate($page->description, 500, ['type' => 'sentence'])?></p>
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

You can also preload assets via `<link>` tags. Preloading assets works with CSS, JS, and font files. CSS and JS will have the correct cache busting parameter appended to the URL

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

<p>Here is a random string <?=$this->wireRandom()->alphanumeric()?></p>

<!-- Get an instace of WireTextTools -->

<p><?=$this->wireTextTools()->truncate($page->text_field, 250)?></p>

<!-- Create a new WireArray instance -->

<p>Buildings taller than 50 feet:</p>
<?php foreach ($this->wireArray($somePage, $anotherPage, $lastPage)->filter('height>=50') as $building): ?>
  <p><?=$building->title?></p>
<?php endforeach ?>
```
