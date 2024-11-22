# Plates For ProcessWire
## Use Plates by The League of Extraordinary Packages with ProcessWire

Plates is a PHP native lightweight templating system that with no compiling, interpreting, or caching. It's pure PHP with no templating language to learn. This approach makes it a highly qualified companion ProcessWire's powerful yet easy to use API. On top of that, Plates was developed and is maintained by The League of Extraordinary Packages, a group of developers that have produces solid stable open source packages that adhere to modern standards and work with or without frameworks.

What Plates offers:

- Code reuse through layouts, rendered files, and nesting
- Passing data to different files as needed
- Encourages use of native PHP functions
- Optional escaping for safety*
- Extensible with easy to create and use extensions to customize Plates
- Native folder/directory awareness for fast and simple includes and rendering

What Plates for ProcessWire offers:

- Preloaded and globally available ProcessWire API in all of your Plates template files
- Pre-configured for use with the ProcessWire templates directory
- Optional additional assistant/helper functions to add additional powers when working with data
- Optional assistant functions include shortcuts to working with ProcessWire objects designed for ProcessWire

Read the full list of features on the [Plates PHP website](https://platesphp.com/).

## Requirements & Usage

Plates For ProcessWire was created to provide access to Plates in a way that feels native to use with ProcessWire. It is a lightweight instantiation wrapper that preloads the ProcessWire API with Plates to make all ProcessWire objects like `$page`, `$config`, `$user`, etc. ready out of the box.

__This module does not include the Plates package itself.__ This allows you to control the version you use, upgrade when desired, prevent package conflicts, and more easily use native namespacing.

Requirements:

- ProcessWire 3.0+
- Plates
- Plates For ProcessWire
- PHP 8.2+

### Installing

- Install Plates via Composer with `composer require league/plates`
- Download this module and unzip in your modules directory
- Install and choose whether to load the custom assistant functions built for and included with Plates for ProcessWire

By default, Plates is configured to use the ProcessWire `templates/` directory as the base directory for your files. For almost all use cases, this is satisfactory as it aligns with how ProcessWire organizes directories and files.

Should you want to change that, create hook and define your own template location.

```php
wire()->addHookBefore(
    'Plates::initialize',
    fn (HookEvent $e) => $e->arguments('templatesDir') = '/your/preferred/templates/path'
);
```

## Usage With ProcessWire

Familiarize yourself with Plates concepts by reviewing the documentation linked above.

Within the context of ProcessWire, this module provides what is mentioned above, as well as some other fancy stuff.

- A global `$plates` object is now available throughout all templates
- The full ProcessWire API is available throughout all templates
- Additional functions are optionally included according to the setting on the module config page

Configuring Plates can be done in your `ready.php` file. This includes defining directories and adding extensions if desired.

### Example Setup In ProcessWire

#### Directory Structure

Set up your directory structure, add folder definitions in `ready.php`

```
templates/
    components/
        contact_form.php
        image_gallery.php
    layouts/
        main.php
    views/
        home.view.php
home.php
```

#### Defining Folders

In `ready.php`, add the layouts and views folder definitions, example:

```php
$templatesPath = $this->wire('config')->paths->templates;

$plates->addFolder('views', "{$templatesPath}views");
$plates->addFolder('layouts', "{$templatesPath}layouts");
$plates->addFolder('components', "{$templatesPath}components");
```

You can add as many folders as desired, like a `components` directory for reusable code.

#### Update Home To Render A Plates File

Set up `home.php` to act as a controller that invokes Plates to render the view. This is the only line needed in this file.

```php
// home.php
// The views::home.view references the folder that was defined followed by the file without the .php extension
<?= $plates->render('views::home.view')?>
```

#### Create A Layout

In `main.php` add your boilerplate markup:

```html
<?php namespace ProcessWire;

// A nullsafe assigment creates a default value, can be any value
$title ??= null;
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title;?></title>
    <link rel="stylesheet" href="<?=$config->urls->templates?>styles/main.css">
  </head>
  <body>
    <secion class="page-hero">
        <?= $this->section('page_hero');?>
    </section>

    <?= $this->section('content')?>

    <footer>
        <?= $this->section('page_footer');?>
    </footer>
  </body>
</html>
```
**NOTE** - Always include the `namespace ProcessWire` statement at the top of your Plates templates to access the ProcessWire API

#### Add Content And View Specific Markup

Use the `layout` method to import the layout, the define content for section blocks. Any content that is added outside of a section block will be rendered under the Plates reserved `content` keyword section block

Example markup/code to add in `home.view.php`:

```html
<?php namespace ProcessWire;?>

<?php $this->layout('layouts::main', ['title' => $page->title])?>

<?php $this->start('page_hero')?>
    <h1>Welcome to <?=$page->headline?></h1>
    <h2><?=$page->headline2?></h2>
<?php $this->stop()?>

<!-- Any content that is not located within a block is rendered using the Plates reserved keyword 'content' section block -->
<section>
    <?=$page->headline2?>
</section>

<section>
    <ul>
    <?php foreach ($page->repeater as $item):?>
        <li>
            <p><?=$item->book_name?></p>
            <p><?=$item->author_name?></p>
        </li>
    <?php endforeach?>
    </ul>
</section>

<!-- Pass fields, text, an any value as data to any included or rendered Plates file -->
<section>
    <h2>Image Gallery</h2>
    <?php $this->insert('components::image_gallery', [
        'imageField' => $page->images,
        'imageCredits' => $page->photographer_credits,
    ])?>
</section>

<?php $this->start('page_footer')?>
  <?php $this->insert('components::contact_form')?>

  <small>Copyright &copy; <?=date('Y')?>
<?php $this->stop()?>

```

#### Fin.

Rinse and repeat

## Assistant Functions

Plates For ProcessWire comes with predefined functions that are optionally loaded with Plates. If you would like these added, ensure that the checkbox is set on the module config page.

I created these functions to add some extra utility while using Plates with ProcessWire, and to replicate some of the behavior that is present in other templating systems. You don't have to use them, that's why they're optional...

To learn more about functions and data in Plates, refer to the [Plates documentation about using functions](https://platesphp.com/templates/functions/).

Just like Plates' support for batching applying native PHP functions to values, any of the Assistant functions that accept one argument may also be chained. Example with two native PHP functions followed by the custom `first` Assistant function:

```php
$name = ' firewire ';

<?=$this->batch($name, 'trim|strtoupper|first')?> // => outputs 'F'
```

To understand how you can add your own custom functions and features by implementing an Extension, refer to the module's code and the [Plates documentation on Extensions](https://platesphp.com/engine/extensions/).

To see full documentation and docblocks for the custom functions, refer to the `PlatesAssistants.php` file in the `Plates/Extensions` module folder.

Here are the Assistant Functions that can be optionally enabled when you use Plates For ProcessWire and how to use them:

### Boolean

#### Bit
Useful when true or false needs to be output in some way to the page where true and false boolean values can't be, or a value isn't a boolean. Very useful when used with Alpine JS.

```html
<div x-data="
       showMap: <?=$this->bit($page->address)?>,
       init() {
         if (this.showMap) {
            // Initialize a Google map...
         }
       }
     "
>
    <div id="google-map"></div>
</div>
```


### Conditionals

#### Or
Outputs one value if truthy, or a second value if the first is falsey

```html
<h2>Hello, <?=$this->or($person->name, 'would you like to sign up for an account?')?></h2>

<!-- Native alternative -->

<h2>Hello, <?=$person->name ?: 'would you like to sign up for an account?'?></h2>
```

#### Inline If
Similar to ternary statement, but may be used with only one argument or both.

```html

<!-- With one value -->
<div class="text-red-500 hidden <?=$this->if($page->message_type == 'warning', '!block'?>">
    <?=$page->message?>
</div>

<!-- With 2 values -->
<div class="text-red-500 <?=$this->if($page->message_type == 'warning', 'block', 'hidden'?>">
    <?=$page->warning_message?>
</div>

<!-- Alternative to a ternary which always requires two ternary values -->
<div class="text-red-500 <?=$page->message_type == 'warning' ? '!block' : 'hidden'?>">
    <?=$page->message?>
</div>
```

#### Conditional Tag
Outputs one of two tags depending on the truthiness of the first argument

```html
<<?=$this->tagIf($page->headline, 'h2', 'h3'?> class="text-neutral-500">
    <?=$page->headline?>
</<?=$this->ifTag()?>>
```


#### Conditional Attribute
Outputs an attribute if conditional is truthy

```html
<button type="submit" <?=$this->attrIf($form->errors, 'disabled', 'true')?>>
    Submit Form
</button>

#### Conditional Class Attribute
Shorthand for conditional attribute, but assumes class. Can be used with one or both values

```html
<button type="submit" <?=$this->classIf($form->errors, 'border-2 border-red-500', 'bg-blue-200')?>>
    Submit Form
</button>
```


### Numbers

#### Clamp
Returns a number limited to and inclusive of the max and min values

```php
<php $this->clamp(523, 100, 500)?> // => 500
<php $this->clamp(125, 100, 500)?> // => 125
<php $this->clamp(82, 100, 500)?> // => 100

<?php if ($this->clamp($page->number_field, 100, 500) > 200):?>
  <div>The number was over 200</div>
<?php else:?>
  <div>The number was under 200</div>
<?php endif?>
```

#### Divisible By
Returns whether a number can be divided by another

```php
<ul class="grid gap-2 <?=$this->divisibleBy(50, 2) ? 'grid-cols-4' : 'grid-cols-3'?>">
<?php foreach ($page->repeater as $item):?>
    <li>
        <p><?=$item->book_name?></p>
        <p><?=$item->author_name?></p>
    </li>
<?php endforeach?>
</ul>
```

#### Even
Returns whether a number is even or not

```html
<?php if ($this->even($page->number_field)):?>
  <div>Event Steven</div>
<?php else:?>
  <div>Odd Man Out</div>
<?php endif?>
```

#### Odd
Returns whether a number is odd or not

```html
<?php if ($this->odd($page->number_field)):?>
  <div>You're Odd</div>
<?php else:?>
  <div>Equal Slice</div>
<?php endif?>
```

### Arrays/Strings

#### Length
Gets the length of an array, WireArray, or string

```html
<!-- Works with arrays -->
<div>You have <?=$this->length(['book', 'hat', 'sunglasses'])?> items in your cart.</div>

<!-- Works with strings -->
<div>You have <?=$this->length($page->person_name)?> letters in your name.</div>

<!-- Works with WireArray and WireArray derived objects -->
<div>You have <?=$this->length($page->family_members)?> family members coming to dinner.</div>
```

#### Random
Returns a random item in an array, WireArray, or a random letter in a string

```html
<!-- Works with arrays -->
<div>Your lucky number is: <?=$this->random([1, 39, 17, 26])?>.</div>

<!-- Works with strings -->
<div>Your name contains this letter <?=$this->random($page->person_name)?>.</div>

<!-- Works with WireArray and WireArray derived objects -->
<div>You win: <?=$this->random($page->prizes)?>.</div>
```

#### First
Returns the first item in an array, WireArray, or first letter of a string

```html
<!-- Works with arrays -->
<div>The first movie showing is: <?=$this->first(['10:00', '12:30', '15:45'])?>.</div>

<!-- Works with strings -->
<div>Your name starts with the letter <?=$this->first($page->person_name)?>.</div>

<!-- Works with WireArray and WireArray derived objects -->
<div>The winner of the race is: <?=$this->first($page->race_results)?>.</div>
```

#### Last
Returns the last item in an array, WireArray, or last letter of a string

```html
<!-- Works with arrays -->
<div>The last number is: <?=$this->last([1, 2, 3, 4])?>.</div>

<!-- Works with strings -->
<div>The last letter of your name is <?=$this->last($page->person_name)?>.</div>

<!-- Works with WireArray and WireArray derived objects -->
<div>The last train leaves at: <?=$this->last('10:00', '12:30', '15:45')?>.</div>
```

#### Group
Groups an array of objects by property or array of arrays by key, also works with WireArray and
WireArray derived objects. Returns an array keyed by specified property/key

```html
<!-- Works with arrays -->
<div>Members by age: <?=$this->group([
    ['name' => 'Tom', 'age' => 28],
    ['name' => 'Mary', 'age' => 42],
    ['name' => 'Gary', 'age' => 28],
    ['name' => 'Jerry', 'age' => 36],
], 'age')?>.</div>

<!-- Works with objects,  WireArray and WireArray derived objects -->
<div>Books by subject: <?=$this->group($page->books, 'subject')?>.</div>
```

#### Slice
Slices an array, WireArray, or WireArray

```html
<!-- Works with arrays -->
<div>Last numbers: <?=$this->slice([1, 2, 3], 1, 2)?>.</div>

<!-- Works with objects,  WireArray and WireArray derived objects -->
<div>Least favorite books: <?=$this->group($page->books, 3)?>.</div>
```

#### URL
Creates a query string or adds a query to a URL

```html
<a href="<?=$this->slice($page->external_url, ['utm_source' => 'website', 'utm_campaign' => 'info'])?>">Find out more</div>
```

### Wire Objects

#### WireRandom
Creates a WireRandom object and returns for use, memoizes

```html
<div>Here's a random string <?=$this->wireRandom()->alphanumeric()?></div>
```

#### WireArray
Creates a new WireArray object and optionally populates if arguments passed

```html
<div>

</div>
```



