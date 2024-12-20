# Plates For ProcessWire
## Use the Plates templating engine by The League of Extraordinary Packages with ProcessWire

Plates is a PHP native lightweight templating system that with no compiling, interpreting, or caching. It's pure PHP with no templating language to learn. This approach makes it a highly qualified companion ProcessWire's powerful yet easy to use API.

What Plates for ProcessWire offers:

- Preloaded and globally available ProcessWire API in all of your Plates template files
- Pre-configured for use with the ProcessWire templates directory
- Optional additional extensions built just for this module that add powerful and chainable methods to work with data
- Optional extensions include functions designed for working with ProcessWire objects for a more integrated workflow

**NOTE:** This is an early release. Test Plates for ProcessWire in you application thoroughly and report bugs by filing an issue on the Git repository.

## An Introduction to Plates

From the [documentation]():

> Plates is a native PHP template system that’s fast, easy to use and easy to extend. It’s inspired by the excellent Twig template engine and strives to bring modern template language functionality to native PHP templates. Plates is designed for developers who prefer to use native PHP templates over compiled template languages, such as Twig or Smarty.

### Why a ProcessWire module for Plates?

There are many templating languages to choose from, some already have ProcessWire modules to make integration easy. They work for many people, and that's great. What they may be lacking in is the core PHP-first approach that ProcessWire provides developers. Plates provides powerful yet simple tools that feel more at home with ProcessWire than any other templating engine. Whether you've worked with templating engines in the past or not, with or without ProcessWire, you'll be up to speed incredibly fast.

### Syntax?

None. There are no special language constructs or changes to how you write code. No `{{ double_braces }}`  `{$braceWraps}`, just `<?=$var?>`. There's no syntax-specific interpreter to work around, just PHP.

Is it more verbose? a little. If you're choosing a templating language because you want to avoid native shorthand PHP echo tags, then this may not be for you. If you want an _incredibly_ lightweight solution with native-feeling layouts, partials, templates, code reuse, chainable functions, and can work with [PHP's built-in alternative control structure syntax](https://www.php.net/manual/en/control-structures.alternative-syntax.php), then Plates is highly worth considering.

The "Syntax" page in the Plates documentation is just a few style recommendations and best practices that often apply to PHP in general. [You can view the not-really-a-syntax page here](https://platesphp.com/templates/syntax/).

### A note on escaping strings

Escaping values is extremely important for safety in applications developed in a bare framework like Laravel, CakePHP, Nette, etc.

But ProcessWire isn't a bare framework, it's a _content management framework_ native to storing and outputting content safely by default. Unless you turn HTML Entitiy formatting off intentionally for fields, and usually that's done with purpose, you don't have to worry about escaping values returned by ProcessWire.

A good contrast is the [Latte](https://latte.nette.org/) templating engine which forces escaping all values and can't be globally disabled. Unless you include the `|noescape` filter, this string will be double escaped and encoded characters that should not be present on the page may be rendered. Unless you remove the entity encoder Text formatter for each field in ProcessWire, you'll have to add this to every text variable output to the page.
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

Plates For ProcessWire was created to provide access to Plates in a way that feels native to use with ProcessWire. It is a lightweight wrapper that preloads the ProcessWire API with Plates to make all ProcessWire objects like `$page`, `$config`, `$user`, etc. ready out of the box.

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

### That's it. You're ready to use Plates in your ProcessWire projects

### Accessing the Plates Template object outside of a Plates template

In Plates template files, like `home.view.php` above, the file is rendered by Plates and so exists within the `Template` plates object. Any Plates template file output _inside_ the `$plates->templates->render()` method is a Plates `Template` object. This includes views, layouts, components, etc.

There may be rare occasions where you want to access the Template object outside of the current template. Plates For ProcessWire makes this easy.

```php
<?php // site/templates/views/home.view.php

    // The $this objext is now accessible outside of this Template file as $plate
    $plates->exposeTemplate($this);

	$this->layout('layouts::main');
?>
<!-- Template markup and output below -->
```

A good example of accessing the parent Plates template outside of the template itself is when inside the scope of blocks in [RockPageBuilder](https://www.baumrock.com/en/processwire/modules/rockpagebuilder/).

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

Plates for ProcessWire comes pre-packed with extentions that you can optionally add to your project should you want to. These extensions were created specifically for this module and provide a lot of useful tools that complement ProcessWire's objects and API. Each extension can be included when configuring the module.

The Plates for ProcessWire extensions include:

### Functions Extension

This extension introduces a significant number of functions that make formatting and working with data in your templates easier. Many are ready to work with ProcessWire objects so you can execute the same functions on strings, arrays, WireArrays, and WireArray derived objects for a native developer experience. These functions are intended to bring in some of

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