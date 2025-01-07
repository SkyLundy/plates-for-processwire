# Functions Extension

The Functions Extension provides a selection of helper methods to make working with values in templates easier. This extension is specifically designed for use with ProcessWire so Functions that accept arrays generally accept WireArray and WireArray derived objects. Where WireArray objects may have similar methods, these are chainable with `$this->batch()` and work whether WireArray or array is passed to make working with different object types easier and more uniform.

The philosophy behind this extension is that, as with Plates itself, native PHP functions are preferred. Unless a signifivant improvement can be made or use case satisfied, there's not really a need to implement a replication. This extension won't implement a `capitalize()` function when `ucfirst()` already exists and can be batched. Additionally, this extension does not implement functions that take closures as arguments and instead focuses on formatting, conversion, casting, sorting, and working with data structures in template context.

Many of these functions are influenced by [Latte Filters](https://latte.nette.org/en/filters). Any function that accepts one argument can be chained using the `$this->batch()` method call. If a function accepts more than one argument but all arguments beyond the first are optional, it can still be batched. This applies to both standard PHP functions as well as any custom extension functions.

Most functions are null safe where possible and impressive batchable chains can be created using combinations of these functions and native PHP functions.


## append

Appends a one or more values to a string, array, integer, or WireArray

```php
<!-- Appends multiple strings. If appending strings, consider string concatenation or interpolation -->
<?=$this->append('Hello, ', $page->first_name, $page->last_name)?>

<!-- Appends one or more integers and returns an integer, returns 3456 -->
<?=$this->append(3, 4, 5, 6)?>

<!-- Append arrays -->
<?php foreach ($this->append(['Foo', 'Bar'], ['Fizz', 'Buzz'], ['FooBar', 'FizzBuzz']) as $value): ?>
    <p><?=$value?></p>
<?php endforeach ?>

<!-- Mix values, values must be appendable. Appending an array to a string or int will fail -->
<?php foreach ($this->append(['Foo', 'Bar'], 'Fizz', 4, ['FooBar', 'FizzBuzz']) as $value): ?>
    <p><?=$value?></p>
<?php endforeach ?>

<!-- Appending strings to integers will result in placement of zeroes, this returns 404 -->
<?php foreach ($this->append(4, 'oops', 4) as $value): ?>
    <p><?=$value?></p>
<?php endforeach ?>
```

## batchEach

Extends Plates' `batch()` method to each item in an array

```php
<?php foreach ($this->batchEach(['hello THERE', ' <div>how are you?</div>'], 'stripHtml|trim|strtolower|ucfirst') as $text): ?>
    <p><?=$text?></p>
<?php endforeach ?>

<!-- Process large numbers of field values easily -->
<ul>
  <?php foreach ($this->batchEach($movies->explode('summary'), 'stripHtml|trim|strtolower|ucfirst') as $text): ?>
    <li><?=$text?></li>
  <?php endforeach ?>
</ul>
```

## batchArray

Alias for [`batchEach`](#batcheach)

## batchEach

Extends Plates' `batch()` method to each item in an array

```php
<?php foreach ($this->batchEach(['hello THERE', ' <div>how are you?</div>'], 'stripHtml|trim|strtolower|ucfirst') as $text): ?>
    <p><?=$text?></p>
<?php endforeach ?>

<!-- Process large numbers of field values easily -->
<ul>
  <?php foreach ($this->batchEach($movies->explode('summary'), 'stripHtml|trim|strtolower|ucfirst') as $text): ?>
    <li><?=$text?></li>
  <?php endforeach ?>
</ul>
```

## bit

Converts truthy or falsey values to integers `1` and `0` respectively.

Useful when `true` or `false` needs to be output in some way to the page where `true` and `false` boolean aren't output to the page via `echo`, or a value is truthy/falsey that would be better converted to a simple value. Very useful when used with Alpine JS.

**Batchable**

```php
<div x-data="{
       init() {
         if (<?=$this->bit($page->show_map)?>) {
            // ...
         }
       }
     "
>
  <div id="google-map"></div>
</div>
```

## clamp

Returns a number limited to and inclusive of the max and min values

```php
<?=$this->clamp(523, 100, 500)?> // => 500
<?=$this->clamp(125, 100, 500)?> // => 125
<?=$this->clamp(82, 100, 500)?> // => 100
```

## csv

Converts an array of values to CSV string via a single argument function.

**Batchable**

```php
<?=$this->csv('One fish', 'Two fish', 'Red fish', 'Blue fish')?>
<?=$this->batch($this->csv('One fish', 'Two fish', 'Red fish', 'Blue fish'), 'strtolower|ucwords');
```

## detectVideoSvc

Analyzes a video URL and detects whether it is a YouTube video or a Vimeo video. Returns 'youtube', 'vimeo', or null if unable to detect.


```php
<p>
  <?php if ($this->detectVideoSvc($page->video_url) === 'youtube'): ?>
    <a href="<?=$page->video_url?>">Watch the YouTube video</a>
  <?php elseif ($this->detectVideoSvc($page->video_url) === 'vimeo'): ?>
    <a href="<?=$page->video_url?>">Watch the Vimeo video</a>
  <?php else ?>
    The video you're looking for does not exist
  <?php endif ?>
</p>
```

## difference

Subtracts all values in an array or WireArray by property. Accepts values that can be castable to numeric values, including integers and floats as strings. Null values and empty strings counted as 0. Accepts a WireArray if a property is provided as a field accessor.

**Batchable**

```php
<?=$this->difference([300, 100, 20, 50])?> <!-- 130 -->
<?=$this->difference($page->discounts, 'amount_field');
```

## divsBy

Alias for [divisibleBy](#divisibleby)

## divisibleBy

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

## embedUrl

Creates an embed URL from either a YouTube or Vimeo video link. Autodetects service from URL. Optional second argument is an array where URL parameters may be added.

**Batchable**

```php
<iframe src="<?=$this->embedUrl('https://www.youtube.com/watch?v=ODmhPsgqGgQ', ['controls' => 0])?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>

<!-- Vimeo URL. Appends default parameters included by Vimeo when copying the embed code from a video -->
<iframe src="<?=$this->embedUrl('https://vimeo.com/2039264832', ['autoplay' => 1])?>" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write"></iframe>
```

## eq

Alias for [`nth`](#nth)

## even

Returns whether a number is even or not

**Batchable**

```php
<p><?=$this->even($page->number) ? 'Even Steven' : 'Odd Man Out'?></p>
```

## falseToNull

Converts a value of `false` to `null`, all other values return original value

**Batchable**

```php
<div><?=$this->falseToNull($page->has_a_pet)?></div>
```

## filterWireNull

Removes all instances of WireNull from WireArray and WireArray derived objects. Returns a new WireArray instance. Useful for reducing the need for secondary checking for `id` or `if` statements before outputting data to the page.

**Batchable**

```php
<ul>
  <?php foreach ($this->filterWireNull($page->pets) as $pet): ?>
    <li><?=$pet->name?></li>
  <?php endforeach ?>
</ul>

```

## first

Returns the first item in an array, WireArray, first character in a string, or first number in an integer, null safe.

Optional second boolean argument will remove all null or WireNull items from the array before getting the first item.

Alias for `nth` function index at 0. See [`nth`](#nth) for type handling and examples of working with nth values

**Batchable**

```php
<!-- Works with arrays, outputs '10:00' -->
<p>The first movie showing is: <?=$this->first(['10:00', '12:30', '15:45'])?>.</p>

<!-- Works with strings -->
<p>Your name starts with the letter <?=$this->first($page->person_name)?>.</p>

<!-- Works with numbers, outputs '5' -->
<p>The first digit is <?=$this->first('52371')?>.</p>

<!-- Works with WireArray and WireArray derived objects -->
<p>True first item, nulls removed: <?=$this->first($page->race_results, true)->title?>.</p>
```

## flatten

Flattens an array containing arrays. By default flattens only first level of items, second boolean value of true flattens all arrays and descendent arrays.

Will also flatten WireArrays or WireArray derived objects containing WireArrays or WireArray derived objects.

```php
<!--
Assuming:
$words = [
  'Foo',
  [
    'Fizz',
    'Buzz',
  ],
  'Bar',
  [
    'FizzBuzz',
    [
      'FooBar',
    ]
  ]
];
-->
<ul>
  <?php foreach ($this->flatten($words) as $word): ?>
    <li><?=$word?></li>
  <?php endforeach ?>
</ul>

<!--
Assuming a WireArray containing two child PageArrays
-->
<ul>
  <?php foreach ($this->flatten($selectedPages) as $selectedPage): ?>
    <li><?=$page->title?></li>
  <?php endforeach ?>
</ul>
```

## from1

Reindexes an iterable to start at index 1

```php
<h1>Race Results</h1>
<table>
  <thead>
    <tr>
      <th scope="col">Placement</th>
      <th scope="col">Name</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($this->from1($participants) as $place => $name): ?>
      <tr>
        <td><?=$place?></td>
        <td><?=$name?></td>
      </tr>
    <?php endforeach ?>h
  </tbody>
</table>
```

## group

Groups an array of objects by property or array of arrays by key, also works with WireArray and WireArray derived objects. Returns an array keyed by specified property/key

Results may be optionally key sorted by passing true, 'asc', or 'desc' as the third argument

```php
<!--
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

<!-- Group WireArray objects by property. If group was executed on a WireArray or WireArray derived object, grouped items will be new WireArrays -->
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

## isRoot

Checks if a given page is the root page, if no page is provided then checks if the current page is the root page

```php
<?=$this->isRoot($page) ? 'Welcome home!' : 'Not home yet...'?>

<!-- Omit the page to check if the current page is root -->
<?=$this->isRoot() ? 'Welcome home!' : 'Not home yet...'?>
```

## isWireArray

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

## jsonDecodeArray

Makes decoding to an array a batchable by eliminating the second boolean argument of PHP's `json_decode()` function while retaining transparency for all other function parameters.

**Batchable**

```php
<ul>
  <?php foreach ($this->jsonDecodeArray($libraryApiResponse) as $author => $books): ?>
    <li>
      <h2><?=$author?></h2>
      <ul>
        <?php foreach ($books as $book): ?>
          <li><?=$book['title']?></li>
        <?php endforeach ?>
      </ul>
    </li>
  <?php endforeach ?>
</ul>
```

## last

Returns the last item in an array, WireArray, or last character in a string, null safe

Optional second boolean argument will remove all null or WireNull items from the array before getting the first item.

Alias for `nth` function retrieving last item by index. See [`nth`](#nth) for type handling and examples of working with nth values

**Batchable**

```php
<!-- Works with arrays -->
<p>The last movie showing is: <?=$this->last(['10:00', '12:30', '15:45'])?>.</p>

<!-- Works with strings -->
<p>Your name ends with the letter <?=$this->last($page->person_name)?>.</p>

<!-- Works with WireArray and WireArray derived objects, passing true as second object removes WireNull items first -->
<p>True last item, nulls removed: <?=$this->last($page->race_results, true)->title?>.</p>
```

## length

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

## merge

Merges an arbitrary number of arrays or WireArray objects into one. Must all be of the same type.

**Batchable**

```php
<!-- Works with arrays -->
<php foreach($this->merge(['a hat', 'sunglasses'], ['sunscreen', 'a towel']) as $item):
  <p>Always Bring <?=$item?> to the beach.</p>
<php endforeach ?>

<!-- Works with WireArray and WireArray derived objects -->
<p>There are <?=$this->merge($page->availableTickets, $page->soldOutTickets)->count()?> total event tickets.</p>
```

## nth

Gets the value at the nth position of an array, string, integer, float, or WireArray,

Return type matches input type.

```php
<!-- Works with arrays, returns '12:30' -->
<p>The first movie showing is: <?=$this->nth(['10:00', '12:30', '15:45'], 1)?>.</p>

<!-- Nulls removed returns 15:45 -->
<p>The second movie showing is: <?=$this->nth(['10:00', null, '15:45'], 1, true)?>.</p>

<!-- Works with strings -->
<p>The second letter in your name is: <?=$this->nth($page->person_name, 1))?>.</p>

<!-- Works with integers, returns integer 9 -->
<p>Lucky number: <?=$this->nth(8392194, 2)?>.</p>

<!-- Integers passed as strings will return a string, returns '1' -->
<p>Lucky number: <?=$this->nth('8392194', 4)?>.</p>

<!-- Works with floats, returns integer 4 -->
<p>Lucky number: <?=$this->batch($this->nth(3.14159, 3), 'strtoupper')?>.</p>

<!-- Will return the decimal if at nth, returns string '.' -->
<p>Lucky number: <?=$this->batch($this->nth(3.14159, 1), 'strtoupper')?>.</p>

<!-- Works with WireArray and WireArray derived objects, nulls removed with third true argument -->
<p>True 4th item, nulls removed: <?=$this->nth($page->race_results, 3, true)->title?>.</p>
```

## nthEnd

Gets the item at the nth location from the end of an array, string, integer, float, or WireArray

See [`nth`](#nth) for type handling and examples of working with nth indexes

## nth1

Gets the nth item in an array, string, integer, float, or WireArray from index beginning at 1 rather than 0

See [`nth`](#nth) for type handling and examples of working with nth indexes

## nth1End

Gets the item at the nth location from the end of an array, string, integer, float, or WireArray indexed from 1 rather than 0

See [`nth`](#nth) for type handling and examples of working with nth indexes

## odd

Returns whether an integer is odd or not

**Batchable**

```php
<p><?=$this->odd($page->number) ? "You're Odd" : 'Equal Slice'?></p>
```

## product

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

## random

Returns a random item in an array, WireArray, character in a string, number in an integer, or value in a float.

Does not generate cryptographically secure values

Uses [`nth`](#nth) for selecting at a randomized index, refer to [`nth`](#nth) for type handling

**Batchable**

```php
<!-- Works with arrays -->
<p>Your lucky number is: <?=$this->random([1, 39, 17, 26])?>.</p>

<!-- Works with strings -->
<p>Your name contains this letter <?=$this->random($page->person_name)?>.</p>

<!-- Works with WireArray and WireArray derived objects -->
<p>Congratulations, you win: <?=$this->batch($page->prizes, 'random')?>.</p>
```

## reverse

Reverses strings, integers, floats, arrays, and WireArrays, null safe

Return type will match input type. Integers return integers, floats return floats.

**Batchable**

```php
<!-- Works with arrays -->
<p><?=$this->reverse([1, 39, 17, 26])?>.</p>

<!-- Works with strings -->
<p>Your name, but backwards <?=$this->reverse($page->person_name)?>.</p>

<!-- Works with floats, returns 41.3 -->
<p>Backwards Pi <?=$this->reverse(3.14)?>.</p>

<!-- Works with WireArray and WireArray derived objects, useful for batching -->
<p>Last place: <?=$this->batch($page->contestants, 'reverse')->title?>.</p>
```

## singleSpaced

Converts all instances of multiple spaces in strings to single spaces.

**Batchable**

```php
<!-- Works with arrays, returns 'who wrote this sentence?' -->
<p><?=$this->collapseSpaces('who   wrote  this  sentence?')?>.</p>

<!-- Batchable, outputs: 'Who wrote this sentence?' -->
<p>This sentence has been cleaned up <?=$this->batch('   who WROTE this   sentence? ', 'trim|singleSpaced|strtolower|ucfirst')?></p>
```

## slice

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

## stripHtml

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
    <li><?=$spaceFact?></li>
  <?php endforeach ?>
</ul>
```

## sum

Returns the sum of all numbers in an array, associative array, WireArray, or WireArray derived object

**Batchable**

```php
<!-- Gets sum of values in a list array, outputs 53 -->
<div>Total cups of coffee consumed last week: <?=$this->sum([1, 4, 9, 2, 3, 10, 24])?></div>

<!-- Gets sum of values in an associative array or array of objects, outputs 6 -->
 Number of cocktails consumed at the James Bond Goldeneye reunion: <?=$this->sum([
  ['name' => 'James', 'drinks' => 2],
  ['name' => 'Natalia', 'drinks' => 3],
  ['name' => 'Boris', 'drinks' => 1],
], 'beers')?>

<!-- Gets sum of values from a WireArray -->
<p>Total points in high scores: <?=$this->product($pages->find('template=high_scores,points>=10'), 'points')?></p>
```

## toList

Converts an iterable value to an indexed array. Associative arrays are returned indexed without keys. WireArray and WireArray derived object are returned as arrays without any keys that may or may not be present when using the `WireArray::getArray()` method.

This is useful for creating an iteration counter.

**Batchable**

```php
<!--
  Calling getArray() when working with image fields returns urls as keys and PageImage objects. Using toList will return an indexed array of PageImage objects
-->
<ul>
  <?php foreach ($this->toList($page->sponsor_images) as $i => $image): ?>
    <li>
      <?php if ($i === 0): ?>
        <h2>Top Sponsor</h2>
      <?php endif ?>
      <img src="<?=$image->url"?>" alt="<?=$image->description?>">
    </li>
  <?php endforeach ?>
</ul>
```

## toObject

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

<!-- Object with fluent access -->
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

## truncate

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

## unique

Returns only unique instances of values in a string, array, int, float, WireArray, or WireArray derived objects. The `WireArray::unique()` method is used when WireArray and WireArray derived objects are passed.

**Batchable**

```php
<!-- Strings, returns 'abc' -->
<p>Unique letters: <?=$this->unique('aabbcc')?></p>

<!-- Unique numbers, returns 3852 -->
<p>Unique letters: <?=$this->unique(33885555552)?></p>
```

## url
Creates a query string or adds a query to a URL

```php
<a href="<?=$this->url($page->external_url, ['utm_source' => $page->title, 'utm_campaign' => $page->campaign])?>">Find out more</a>
```

## urlIsExternal

Checks if a given URL is located on the same domain as the site. Returns boolean by default. Optional second parameter value will be returned if true, optional third parameter value will be returned if false.

Ignores http/https protocol, different subdomains on the same domain will be identified as an external URL

```php
<!-- With value to return if true -->
<?php foreach ($page->favorite_links as $link): ?>
  <a href="<?=$link?>" class="<?=$this->urlIsExternal($link, 'link-out')?>"></a>
<?php endforeach ?>
```

## vimeoEmbedUrl

Creates an embed URL from a Vimeo video link. Optional second argument is an array where URL parameters may be added.

See [embedUrl](#embedurl) to create URLs for Vimeo or YouTube with autodetection

**Batchable**

```php
<!-- Vimeo URL. Appends default parameters included by Vimeo when copying the embed code from a video, may be removed by passing the parameter with a null value in the second argument -->
<iframe src="<?=$this->embedUrl('https://vimeo.com/2039264832', ['autoplay' => 1])?>" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write"></iframe>
```

## withChidren

Gets all children for and prepends the given Page object or Page found by selector.

```php
<ul>
  <?php foreach ($this->appendChildren('/') as $topLevelPage): ?>
    <li>
      <a href="<?=$topLevelPage->url?>"><?=$topLevelPage->title?></a>
    </li>
  <?php endforeach ?>
</ul>

<!-- Optional second child page selector -->
<ul>
  <?php foreach ($this->appendChildren('template=all_events', 'template=event,featured_event=1') as $eventPage): ?>
    <li>
      <a href="<?=$eventPage->url?>"><?=$eventPage->title?></a>
    </li>
  <?php endforeach ?>
</ul>

<!-- Accepts a page object as the first argument -->
<ul>
  <?php foreach ($this->appendChildren($page) as $subnavPage): ?>
    <li>
      <a href="<?=$subnavPage->url?>"><?=$subnavPage->title?></a>
    </li>
  <?php endforeach ?>
</ul>
```

## youTubeEmbedUrl

Creates an embed URL from a YouTube video link. Optional second argument is an array where URL parameters may be added.

See [embedUrl](#embedurl) to create URLs for Vimeo or YouTube with autodetection

**Batchable**

```php
<iframe src="<?=$this->embedUrl('https://www.youtube.com/watch?v=ODmhPsgqGgQ', ['controls' => 0])?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
```