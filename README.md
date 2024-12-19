# Plates For ProcessWire
## Use the Plates templating engine by The League of Extraordinary Packages with ProcessWire

Plates is a PHP native lightweight templating system that with no compiling, interpreting, or caching. It's pure PHP with no templating language to learn. This approach makes it a highly qualified companion ProcessWire's powerful yet easy to use API. On top of that, Plates was developed and is maintained by The League of Extraordinary Packages, a group of developers that produce high quality stable open source packages that adhere to modern standards and work with or without frameworks.

What Plates offers:

- Code reuse through layouts, rendered files, and nesting
- Passing data to different files as needed
- Encourages use of native PHP functions
- Optional escaping for safety
- Extensible with easy to create and use extensions to customize Plates
- Native folder/directory awareness for fast and simple includes and rendering

What Plates for ProcessWire offers:

- Preloaded and globally available ProcessWire API in all of your Plates template files
- Pre-configured for use with the ProcessWire templates directory
- Optional additional extensions built just for this module that add powerful and chainable methods to work with data
- Optional extensions include functions designed for working with ProcessWire objects for a more integrated workflow

Read the full list of features on the [Plates PHP website](https://platesphp.com/).

**NOTE:** This is an early release. Test Plates for ProcessWire in you application thoroughly and report bugs by filing an issue on the Git repository.

## An Introduction to Plates

From the [documentation]():

> Plates is a native PHP template system that’s fast, easy to use and easy to extend. It’s inspired by the excellent Twig template engine and strives to bring modern template language functionality to native PHP templates. Plates is designed for developers who prefer to use native PHP templates over compiled template languages, such as Twig or Smarty.

### Why create a ProcessWire module for Plates?

There are many templating languages to choose from, some already have ProcessWire modules to make integration easy. They work for many people, and that's great. What they may be lacking in is the core PHP-first approach that ProcessWire provides developers. Plates provides powerful yet simple tools that feel more at home with ProcessWire than any other templating engine. Whether you've worked with templating engines in the past or not, with or without ProcessWire, you'll be up to speed incredibly fast.

### Syntax?

None. There are no special language constructs or changes to how you write code. No `{{ double_braces }}`  `{$braceWraps}`, just `<?=$var?>`. There's no syntax-specific interpreter to work around, just PHP.

Is it more verbose? a little. If you're choosing a templating language because you want to avoid native shorthand PHP echo tags, then this may not be for you. If you want an _incredibly_ lightweight solution with native-feeling layouts, partials, templates, code reuse, chainable functions, and can work with [PHP's built-in alternative control structure syntax](https://www.php.net/manual/en/control-structures.alternative-syntax.php), then Plates is highly worth considering.

The "Syntax" page in the Plates documentation is just a few style recommendations and best practices that often apply to PHP in general. [You can view the not-really-a-syntax page here](https://platesphp.com/templates/syntax/).

### A note on escaping strings

Escaping values is extremely important for safety in applications developed in a bare framework like Laravel, CakePHP, Nette, etc.

But ProcessWire isn't a bare framework, it's a _content management framework_ native to storing and outputting content safely by default. ProcessWire handles this with Text formatters. Unless you turn HTML Entitiy formatting off intentionally, you don't have to worry about escaping values returned by ProcessWire.

A good contrast is the [Latte](https://latte.nette.org/) templating engine which forces escaping all values which can't be globally disabled. Unless you include the `|noescape` filter, this string will be double escaped and encoded characters that should not be present on the page may be rendered. Unless you intentionally remove the entity encoder Text formatter for each field in ProcessWire, you'll have to add this to every text variable output to the page.
```php
<title>{$page->title|noescape}</title>
```

With ProcessWire and Plates:
```php
<title><?=$page->title?></title>
```

So to re-answer the question "is it more verbose?"… maybe not after all.

If you do need to escape a value, Plates makes it easy:
```php
<title><?=$this->e($page->your_field)?></title>
```

## Requirements & Usage

Plates For ProcessWire was created to provide access to Plates in a way that feels native to use with ProcessWire. It is a lightweight instantiation wrapper that preloads the ProcessWire API with Plates to make all ProcessWire objects like `$page`, `$config`, `$user`, etc. ready out of the box.

**This module does not include the Plates package itself.** This allows you to control the version you use, upgrade when desired, and keep this module from requiring "upkeep" releases when Plates is updated. Plates for ProcessWire is just an adapter that provides the connection between Plates and your ProcessWire application.

Requirements:

- ProcessWire 3.0+
- Plates
- Plates For ProcessWire
- PHP 8.2+

### Installing

- Install Plates via Composer with `composer require league/plates`
- Download this module and unzip in your modules directory
- Install and choose whether to load the custom Extensions built for and included with Plates for ProcessWire

## Using Plates With ProcessWire

Before getting started with Plates in ProcessWire, it's highly recommended that you review the (very short and simple) documentation to get a feel for how Plates works, its features, and approach to templating. After reviewing that information, come back and see how Plates for ProcessWire will work for your application or website.

You may skip any steps that create new objects such as `Engine` or manually creating templates. Plates for ProcessWire handles all of that for you.

[View the Plates documentation here](https://platesphp.com/getting-started/simple-example/)

### How to use Plates in your ProcessWire templates

Using Plates for ProcessWire is very simple. The module handles creating the Plates object and registers all ProcessWire variables/functions.  Plates For ProcessWire provides the following

- A global `$plates` object that references the module
- The full ProcessWire API to all Plates template and layout files
- Optional helper functions included via custom Extensions included with this module that can be added on the module config page if desired

Plates for ProcessWire automatically creates the `Engine` object and specifies the `templates` directory for you. The following shows how the concepts outlined in the Plates documentation have been adapted for use in ProcessWire.

Where the Plates documentation references the variable `$templates`, use `$plates->templates` instead.

### Creating Folders

Documentation reference: [Folders in Plates](https://platesphp.com/engine/folders/).

Assuming this directory structure:
```
site/
  templates/
    components/
      image_gallery.php
    layouts/
      main.php
    views/
      home.view.php
    home.php
```

Folders can be registered in `ready.php`

```php
$templatesDir = $config->paths->templates;

$plates->templates->addFolder('components', "{$templatesDir}components");
$plates->templates->addFolder('layouts', "{$templatesDir}layouts");
$plates->templates->addFolder('views', "{$templatesDir}views");
```

### Rendering a Plates template within your ProcessWire template

Documentation reference: [Simple Example](https://platesphp.com/getting-started/simple-example/)

Rendering templates in Plates is both flexible and simple. We use ProcessWire templates as a "controller" that will render markup from Plates template files. To do this we add a single line to our ProcessWire template that renders a Plates template.

```php
// /site/templates/home.php
<?=$plates->templates->render('views::home.view')?>
```

As mentioned, the entire ProcessWire API is made available to all Plates files so there is no need to pass any specific ProcessWire objects. In the event you need to pass additional data, that can be done as the documentation specifies.

```php
// site/templates/home.php
<?=$plates->templates->render('views::home.view', ['yourVariable' => 'Hello'])?>
```

### Layouts, Nesting, Sections, etc.

Documentation reference: [Layouts](https://platesphp.com/templates/layouts/)
Documentation reference: [Nesting](https://platesphp.com/templates/nesting/)
Documentation reference: [Sections](https://platesphp.com/templates/sections/)

As with all files handled by Plates, the entire ProcessWire API is available. The `$this` keyword references the current template object and provides access to the Plates API. Note that multilanguage support is also available.

The `$page` variable will always reference the current page being rendered, regardless of file.

**Layout**

```php
<?php // site/templates/layouts/main.php

// A nullsafe assigment creates a default value if needed.
$title ??= $page->title;
$logo ??= null;
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title>
    <link rel="stylesheet" href="<?=$wire->urls->assets?>bundle/styles/app.css">
  </head>
  <body>
    <header>
        <?php if ($logo): ?>
    		<img src="<?=$logo->url?>" alt="<?=$logo->description?>">
		<?php endif ?>
    </header>
    <secion class="page-hero">
        <?=$this->section('page_hero')?>
    </section>

    <?=$this->section('content')?>

    <footer>
        <?=$this->section('page_footer')?>

        <small><?=__('Copyright')?> &copy; <?=date('Y')?></small>
    </footer>
    <script src="<?$wire->urls->assets?>bundle/scripts/app.js"></script>
  </body>
</html>
```

**Nested Template**

```php
<?php // site/templates/components/image_gallery.php

/**
 * @var Pageimages $images
 * @var string $imageCredits
 */
?>
<div class="image-gallery">
    <div class="images">
        <?php foreach ($images as $image): ?>
            <img src="<?=$image->url?>" alt="<?=$image->description?>">
        <?php endforeach ?>
    </div>
    <div class="image-credits">
        Photos provided by <?=$imageCredits?>
    </div>
</div>
```

**Plates Template**

Rendered in your ProcessWire template via `<?=$plates->templates->render('views::home.view')?>`

Additional documentation reference: [Functions](https://platesphp.com/templates/functions/)

```php
<?php // site/templates/views/home.view.php
	$this->layout('layouts::main', ['title' => $page->title]);
?>
<?php $this->start('page_hero')?>
    <h1><?=$page->headline?></h1>
    <h2><?=$page->headline2?></h2>
<?php $this->stop()?>

<!-- Anything not located within a named section is rendered in the Plates reserved keyword 'content' section in a layout -->
<section>
    <?=$this->e($page->dangerous_field)?>
    <?=$page->body?>
</section>

<section>
    <?=$this->batch($page->text, 'strtolower|ucfirst')?>
</section>

<section>
    <ul>
    <?php foreach ($page->library as $item):?>
        <li>
            <p><?=$item->book_name?></p>
            <p><?=$item->author_name?></p>
        </li>
    <?php endforeach?>
    </ul>
</section>

<!-- You may use ProcessWire's translation features as well -->
<section>
    <h2><?=__('Image Gallery')?></h2>
    <?=$this->fetch('components::image_gallery', [
        'images' => $page->images,
        'imageCredits' => $page->image_credits,
    ])?>
</section>

<?php $this->start('page_footer')?>
  <form>
      <label>
        Sign up for our newsletter
      	<input type="text">
      </label>
      <input type="submit">
  </form>
<?php $this->stop()?>

```

## Accessing the Plates Template object outside of a Plates template

In Plates template files, like `home.view.php` above, the file is rendered by the Plates engine and thusly exists within the `Template` plates object. Any Plates template file output _inside_ the `$plates->templates->render()` method is a Plates `Template` object. This includes views, layouts, components, etc.

There may be rare occasions where you want to access the Template object outside of the current template. Plates For ProcessWire makes this easy.

```php
<?php
	// site/templates/views/home.view.php

    // $this is now accessible outside of this Template file using the global $plate variable
    $plates->exposeTemplate($this);

	$this->layout('layouts::main', ['title' => $page->title]);
?>
<!-- Template markup and output below -->
```

**Example**

An example of where exposing the current template is useful is when using [RockPageBuilder](https://www.baumrock.com/en/processwire/modules/rockpagebuilder/). When rendering a RockPageBuilder field, accessing the parent Plates template is not possible by default. By calling the `$plates->exposeTemplate($this)` method in the template that is rendering the RockPageBuilder field, you can then use all of the methods available in your Plates template with the `$plate` variable. 

```php
<?php namespace ProcessWire;
// site/templates/views/home.view.php

$plates->exposeTemplate($this);
?>
<!-- ...markup and page content -->
<?=$page->rockpagebuilder_blocks->render(true)?>
<!-- ...markup and page content -->
```

```php
<?php namespace ProcessWire;
// site/templates/RockPageBuilder/blocks/Text.view.php
?>
<section class="rpb-text <?=$block->classes()?>" <?=alfred($block)?>>

    <h2><?=$plate->batch($block->title(), 'strtolower|ucwords')?></h2>

    <?=$plate->fetch('components::rte_field', ['content' => $block->body])?>

</section>

```

## Custom Functions & Extensions

Documentation reference: [Functions](https://platesphp.com/engine/functions/)
Documentation reference: [Extensions](https://platesphp.com/engine/extensions/)

Plates offers clear documentation for implementing your own functions and extensions. In ProcessWire, the best place to register these is in your `/site/ready.php` file where Plates for ProcessWire is loaded and ready ahead of page render.

**Registering Functions**

```php
<?php namespace ProcessWire;
// site/ready.php

$plates->templates->registerFunction('concatFields', function(Page $page, ...$fieldNames) {
	$fieldValues = array_map(fn ($fieldName) => trim($page->$fieldName ?? ''), $fieldNames);

    return implode($fieldValues);
});
```

**Registering Extensions**

```php
<?php namespace ProcessWire;
// site/ready.php

// Import your custom extension class with `require_once` or creating a namespace in ProcessWire and the `use` statment
require_once __DIR__ . 'some/folder/MyCustomPlatesExtension.php';

$plates->templates->loadExtension(new MyCustomPlatesExtension());
```

## Plates for ProcessWire Extensions

Plates for ProcessWire comes with custom extensions pre-built for use in your Plates template files. These extensions are optional and can be enabled when configuring the module.

These extensions are purely provided as quality-of-life additions and are not necessary to use Plates in your ProcessWire project.

---

### Conditionals Extension

Helper methods that supplement the [alternate control structures](The "Syntax" page in the Plates documentation is just a few style recommendations and best practices that often apply to PHP in general. [You can view the not-really-a-syntax page here](https://platesphp.com/templates/syntax/).) provided by PHP that are useful when rendering markup. Many of these are influenced by [Latte Tags](https://latte.nette.org/en/tags).

#### Switch

Outputs value in an array where key matches the first argument passed

```php
<div class="<?=$this->switch($page->favorite_color, ['red' => 'bg-red-500', 'yellow' => 'bg-amber-500', 'green' => 'bg-emerald-500'])?>">
    Hello!
</div>
```

#### Conditional Tag

Outputs one of two tags depending on the truthiness of the first argument

```php
<<?=$this->tagIf($page->headline, 'h3', 'h2'?> class="text-neutral-500">
    <?=$page->headline2?>
</<?=$this->ifTag()?>>
```

#### Conditional Attribute

Outputs an attribute if conditional is truthy. Second argument is the attribute. Optional third value is the attribute value.

```php
<button type="submit" <?=$this->attrIf($form->errors, 'disabled')?>>
    Submit Form
</button>
```

#### Conditional Class Attribute

Shorthand for conditional attribute, but assumes class. Can be used with one or both values

```php
<button type="submit" <?=$this->classIf($form->errors, 'border-2 border-red-500', 'bg-blue-200')?>>
    Submit Form
</button>
```

---

### Functions Extension

The Functions Extension provides an selection of helper functions to make working with values easier. It is also built for ProcessWire specifically. Functions that accept arrays also accept WireArray and WireArray derived objects. While WireArray objects may have similar methods, these are chainable with `$this->batch()` and work whether WireArray or array is passed to make working with different object types easier.

Many of these functions are influenced by [Latte Filters](https://latte.nette.org/en/filters). Any function that accepts one argument can be chained using the `$this->batch()` method call. If functions accept more than one argument but may still execute when only one argument is passed, it can still be batched. This applies to both standard PHP functions as well as any custom extension functions.

Most functions are null safe where possible and impressive batchable chains can be created using combinations of these functions and native PHP functions.

Extension functions that can be batched are noted with **Batchable** for easy reference

#### Batch Array

Extends Plates-like `batch()` method to each item in an array

```php
<?php foreach ($this->batchArray(['hello THERE', ' <div>how are you?</div>'], 'stripHtml|ucfirst') as $text): ?>
    <p><?=$text?></p>
<?php endforeach ?>

<!-- Process large numbers of field values easily -->
<?php foreach ($this->batchArray($activities->explode('summary'), 'stripHtml|ucfirst') as $text): ?>
    <p><?=$text?></p>
<?php endforeach ?>
```

#### Bit

Useful when `true` or `false` needs to be output in some way to the page where `true` and `false`boolean values won't be output to the page via `echo`, or a value isn't a boolean. Very useful when used with Alpine JS.

**Batchable**

```php
<div x-data="{
       showMap: <?=$this->bit($page->address)?>,
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

#### Clamp

Returns a number limited to and inclusive of the max and min values

```php
<php $this->clamp(523, 100, 500)?> // => 500
<php $this->clamp(125, 100, 500)?> // => 125
<php $this->clamp(82, 100, 500)?> // => 100
```

#### Divisible By

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

#### Even

Returns whether a number is even or not

**Batchable**

```php
<div><?=$this->even($page->number) ? 'Even Steven' : 'Odd Man Out'?></div>
```

#### Odd

Returns whether a number is odd or not

**Batchable**

```php
<div><?=$this->odd($page->number) ? "You're Odd" : 'Equal Slice'?></div>
```

#### Length

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

#### First

Returns the first item in an array, WireArray, or first character in a string, null safe 

**Batchable**

```php
<!-- Works with arrays -->
<div>The first movie showing is: <?=$this->first(['10:00', '12:30', '15:45'])?>.</div>

<!-- Works with strings -->
<div>Your name starts with the letter <?=$this->first($page->person_name)?>.</div>

<!-- Works with WireArray and WireArray derived objects -->
<div>The winner of the race is: <?=$this->batch($page->race_results, 'first')?>.</div>
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

#### Group

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

## Tips for tidy templates

There are many native language constructs in PHP that can clean up your templating further, no syntactic sugar required. Employing these makes for cleaner and easier to read templates.

```php
// Rendering values
// Instead of this:
<?php echo $page->field; ?>

// Short echo
<?=$page->field?>

// Control structures
// Instead of this:
<?php if ($page->field): ?>
    <?=$page->field?>
<?php endif; ?>

// Ternary. The null value may also be a fallback value
<?=$page->field ? $page->field : null?>

// Elvis, a shorter ternary
<?=$page->field ?: null?>

// Nullsafe operator, when a variable may be null and a property needs to be accessed. Can be combined with an Ternary or Elvis operator
<?=$person?->name?>
// Nested
<?=$person?->name?->first?>

// Multiple values
// Instead of this:
Hello <?=$page->first_name?> <?=$page->first_name?>!

// Try this:
Hello <?="{$page->first_name} {$page->last_name}"?>!

// String interpolation lets you execute functions
Hello <?=trim("{$page->first_name} {$page->last_name}")?>

Use string interpolation to batch
<?php $fullName = trim("{$page->first_name} {$page->last_name}") ?>
<?=ucwords($fullName)?>

Try this:
<?=$this->batch("{$page->first_name} {$page->last_name}", 'trim|ucwords')?>

```