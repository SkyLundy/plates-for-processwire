# Plates For ProcessWire

**Use the Plates templating system by The League of Extraordinary Packages with ProcessWire**

Plates is a PHP native lightweight templating system that with no compiling, interpreting, or transpiler caching layer. It's pure PHP templating with no syntax to learn and feels at home with ProcessWire.

Plates for ProcessWire offers:

- Preloaded and globally available ProcessWire API objects in all of Plates template files
- Pre-configured for use with the ProcessWire templates directory
- Enable and disable core Plates extensions via the module config page
- Optional additional custom extensions built for this module that add powerful and chainable methods to work with data, can be enabled and disabled via the module config page

## An Introduction to Plates

From the Plates [documentation](https://platesphp.com/):

> Plates is a native PHP template system that’s fast, easy to use and easy to extend. It’s inspired by the excellent Twig template engine and strives to bring modern template language functionality to native PHP templates. Plates is designed for developers who prefer to use native PHP templates over compiled template languages, such as Twig or Smarty.

In practice Plates provides:

- Reusable nestable layouts with inheritance and defined output blocks that receive data
- Rendering for files containing markup that receive data and can be reused and nested
- Chainable filters that make transforming and rendering values more efficient
- No new syntax, familiar code you already write
- No template compiling or interpreting
- Preference for existing PHP functions over alternative implementations
- File path resolution via configurable Folders

## Requirements & Usage

Requirements:

- ProcessWire 3.0+
- PHP 8.2+

Plates for ProcessWire will work if you have installed Plates via Composer but will also work if it is not. If Plates is intalled via Composer, the module will use that version. For those that do not work with Composer, or prefer to not manage the library separately, this module comes packaged with Plates and will fall back to its usage if not detected otherwise.

### Installing

- Download this module and unzip in your modules directory
- Install and configure the module
- Set up folders and templates, build something cool

## How Plates for ProcessWire Works

Plates For ProcessWire is a lightweight wrapper that preloads the ProcessWire API with Plates to make all ProcessWire objects like `$page`, `$config`, `$user`, etc. ready out of the box. Plates For ProcessWire provides the following:

- A global `$plates` object that references the module
- The full ProcessWire API to all Plates template and layout files
- Optional custom Extensions included with this module that can be managed via the module config page

Plates for ProcessWire automatically creates the `Engine` object and specifies `/site/templates` as the root directory where Plates will look for files to render as well as any Folders you define.

## Getting Started

An example is provided that illustrates how to start using Plates in your ProcessWire application, tips on how to make use of optional features, and how to implement additional Plates abilities in ProcessWire.

The example can be found in: `/site/modules/Plates/example/`

By default, Plates for ProcessWire configures Plates to look for files that end with the extension `.plates.php`. This can help with file organization and to easily differentiate between a ProcessWire template and a Plates template. This extension may be changed on the module config page.

### Reviewing the Plates Documentation

It's recommended that you review the (very short and simple) documentation to get a feel for how Plates works, its features, and approach to templating.

You may skip any steps in the documentation that create new objects such as `Engine` or manually creating `Template` objects. Plates for ProcessWire handles all of that for you. Where the Plates documentation references the variable `$templates`, use `$plates->templates` instead.

[View the Plates documentation here](https://platesphp.com/getting-started/simple-example/)

### Accessing the Plates Template object outside of a Plates template

Files rendered with Plates exist are done so  with Plates' `Template` object. Any Plates template file rendered by Plates _inside_ the `$plates->templates->render()` method is a Plates `Template` object and as such, all Plates functions, custom functions, and extensions are accessed using the `$this` object.

There may be occasions where you want to access the Template object outside of the current template. Plates For ProcessWire makes this easy.

```php
<?php namespace ProcessWire;
  // The $this object is now accessible outside of this Template file via the global $plate object
  $plates->exposeTemplate($this);

	$this->layout('layouts::main');
?>
<!-- ...template markup... -->
```

When `$plates->exposeTemplate($this)` is called within a template, a global `$plate` variable is created that provides access to all of the methods and properties available via the `$this` object within a Plates template. Keep in mind that this is created when ProcessWire is rendering the base template so the `$plate` variable may not be available in all contexts such as Page classes and hooks.

A good example of where accessing the parent Plates template elsewhere may be needed is inside [RockPageBuilder](https://www.baumrock.com/en/processwire/modules/rockpagebuilder/) Blocks. If `$plates->exposeTemplate($this)` is used on a template that is rendering a RockPageBuilder field, then the `$plate` variable can be used inside individual block view files.

## Plates for ProcessWire Extensions

Plates for ProcessWire comes pre-packaged with extentions that you can optionally add to your project should you desire. These extensions were created specifically for this module and provide  useful tools that complement ProcessWire's objects and API. Where Plates at heart is a very lightweight templating engine, these extensions aim to add more feature parity by adding functions that act as filters and macros found in other templating engines. In total, these extensions provide over 95 new functions that can be used in Plates templates when used with ProcessWire.

Extensions include:

- **Functions Extension**: A library of useful utility functions for transforming and outputting data. Inspired by functions and filters found in other templating libraries.
- **Conditionals Extension**: Functions to assist with control flow and output depending on conditions
- **Wire Objects Extension**: Functions that instantiate ProcessWire objects for easy use in templates
- **Sanitizer Extension**: A wrapper that provides access to all Sanitizer object methods and makes many of them batchable
- **Asset Loader Extension**: A configurable extension that provides fast and easy linking, inlining, and preloading CSS, JS, and font files with automatic caching parameters added to URIs.
- **Embed Extension**: Adds tools to embed Plates templates like layouts for increased flexibility and code reuse

Each extension can be enabled when configuring the module and documentation for each is provided on the module config page. Documentation can also be viewed in individual markdown files located within `/site/modules/Plates/Extensions/Documentation/`.

## Core and Community Extensions

Plates for ProcessWire includes the ability to enable extensions provided by the Plates library on the module config page. Currently these are:

- [Asset](https://platesphp.com/extensions/asset/)
- [URI](https://platesphp.com/extensions/uri/)

To add [Community Extensions](https://platesphp.com/extensions/community/), install them with Composer and register them with Plates in your `ready.php` file.

## Syntax and Usage Tips

There are no special language constructs or changes to how you write code. No `{{ double_braces }}` or `{$braceWraps}`, just `<?=$var?>`. There's no compilation or interpreter layer, just PHP.

The "Syntax" page in the Plates documentation is just a few style recommendations and best practices that often apply to PHP in general. [You can view the not-really-a-syntax page here](https://platesphp.com/templates/syntax/).

Concise syntax is something that templating engines use to contrast their approach to writing templates vs standard PHP. [Smarty](https://www.smarty.net/syntax_comparison) makes this comparison between its syntax and pure PHP using a long form echo statement with nested function calls.

**PHP (extended syntax)**

```php
<?php echo htmlspecialchars(strtolower($foo),ENT_QUOTES,'UTF-8'); ?>
```

**Plates**

```php
<?=$this->e($foo, 'strtolower')?>
```

In Plates, `e()` is provided as the [escape](https://platesphp.com/templates/escaping/) function. As is noted below, this is redundant and unnecessary in ProcessWire unless you're outputting values from a known unsafe source or fields with output formatting disabled. In almost every scenario, this is safe to use:

```php
<?=strtolower($foo)?>
```

With the `batch()` function that Plates provides, you can execute multiple chained functions on the same value:

```php
<?=$this->batch($foo, 'strtolower|ucwords')?>
```

### A note on escaping strings

Escaping values is extremely important for safety in applications developed in a bare framework like Laravel, CakePHP, Nette, etc.

In contrast, ProcessWire isn't a bare framework, it's a _content management framework_ native to storing and outputting content safely by default. Unless you turn HTML Entitiy formatting off intentionally for fields, and usually that's done with purpose (like intentionally outputting markup/code to a page), you don't have to worry about escaping field values.

A good contrast is the [Latte](https://latte.nette.org/) templating engine which forces escaping all values and can't be globally disabled. Unless you include the `|noescape` filter in most instances, field values will be double escaped and encoded characters that should not be present on the page may be rendered. Unless you remove the entity encoder Text formatter for each field in ProcessWire, you'll have to add this to every text variable output to the page.

```php
<title>{$page->your_field|noescape}</title>
```

With ProcessWire and Plates:

```php
<title><?=$page->your_field?></title>
```

If you do need to escape a value, as mentioned above, Plates makes it easy:
```php
<title><?=$this->e($page->your_field)?></title>
```

### Short tags?

There may be some confusion about the use of "short tags". Templating engines like Smarty [recommend that you don't use them](https://www.smarty.net/syntax_comparison), primarily as an argument for using Smarty. However the statement is slightly misleading. Here's what short tags are and are not:

A short tag opens a PHP document or statement with `<?`
```php
<? echo $variable ?>
```

This is a shorthand echo statement outputs a value, it is not a short tag

```php
<?=$variable?>
```

Short tag use is not recommended as they can be disabled in any `php.ini` configuration. This is the widely accepted compatability concern and PHP documentation itself states they should not be used. If short tags are disabled in any environment, shorthand echo statements will not be affected.

PHP considers `<?php ?>` and `<?= ?>` to be equally standard syntax and [recommends their use](https://www.php.net/manual/en/language.basic-syntax.phptags.php).

### Tips for tidy templates

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
    <p><?=$page->field?></p>
<?php else: ?>
    <p>Fallback value</p>
<?php endif; ?>

// Ternary
<p><?=$page->field ? $page->field : 'Fallback value'?></p>

// Elvis, a shorter ternary if a variable is declared but falsey
<p><?=$page->field ?: 'Fallback value'?></p>

// If a variable may or may not exist, you can output it to the page or safely fall back to another value
<p><?=$maybeExists ?? 'Fallback value'?></p>

// Object property and method calls can be made null safe
<?=$person?->name?->first?>

// Multiple values
// Instead of this:
Hello <?=$page->first_name?> <?=$page->first_name?>!

// Consider string interpolation:
Hello <?="{$page->first_name} {$page->last_name}"?>!

// Use string interpolation with functions
Hello <?=trim("{$page->first_name} {$page->last_name}")?>!

// For multiple functions, instead of this:
<?php $fullName = trim("{$page->first_name} {$page->last_name}") ?>
Hello <?=ucwords($fullName)?>!

// Consider using the Plates batch() function:
Hello <?=$this->batch("{$page->first_name} {$page->last_name}", 'trim|ucwords')?>!
```