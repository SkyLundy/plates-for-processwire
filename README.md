# Plates For ProcessWire
## Use the Plates templating engine by The League of Extraordinary Packages with ProcessWire

Plates is a PHP native lightweight templating system that with no compiling, interpreting, or caching. It's pure PHP with no templating language to learn. This approach makes it a highly qualified companion to ProcessWire's powerful and intuitive API.

What Plates for ProcessWire offers:

- Preloaded and globally available ProcessWire API objects in all of Plates template files
- Pre-configured for use with the ProcessWire templates directory
- Add enable and disable core Plates extensions via the module config page
- Optional additional custom extensions built for this module that add powerful and chainable methods to work with data, can be enabled and disabled via the module config page

## An Introduction to Plates

From the Plates [documentation](https://platesphp.com/):

> Plates is a native PHP template system that’s fast, easy to use and easy to extend. It’s inspired by the excellent Twig template engine and strives to bring modern template language functionality to native PHP templates. Plates is designed for developers who prefer to use native PHP templates over compiled template languages, such as Twig or Smarty.

In practice Plates provides:

- Reusable and nestable layouts with defined output blocks that receive data
- Files containing "component" style markup that receive data and be reused and nested
- Chainable filters that make transforming and rendering values more efficient
- Better readability through organization and syntax
- No template compiling, interpreting, or syntax

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

You may skip any steps in the documentation that create new objects such as `Engine` or manually creating templates. Plates for ProcessWire handles all of that for you.

Where the Plates documentation references the variable `$templates`, use `$plates->templates` instead.

[View the Plates documentation here](https://platesphp.com/getting-started/simple-example/)

### How Plates for ProcessWire Works

Using Plates for ProcessWire is very simple. Plates For ProcessWire provides the following:

- A global `$plates` object that references the module
- The full ProcessWire API to all Plates template and layout files
- Optional custom Extensions included with this module that can be managed via the module config page

Plates for ProcessWire automatically creates the `Engine` object and specifies `/site/templates` as the root directory where Plates will look for files to render.

### Examples To Get Started

This module comes with examples to help illustrate how to use Plates in your templates, tips on how to make use of optional features, and show how to implement extra Plates features in ProcessWire.

There are two examples, a basic example that uses the core features provided by Plates, and an example that uses some of the additional functions available if the custom [Plates for ProcessWire Extensions](#platesforprocesswireextensions) are enabled on the module config page.

Basic: `/site/modules/Plates/examples/basic/`
With Extensions: `/site/modules/Plates/examples/extensions_enabled/`

The "extensions enabled" example uses only a small number of the wide array of useful tools and functions that are available if custom extensions are enabled. For more information, review the notes below or refer to the documentation included on the Plates for ProcessWire module config page.

---

### Accessing the Plates Template object outside of a Plates template

Files rendered with Plates exist are done so  with Plates' `Template` object. Any Plates template file rendered by Plates _inside_ the `$plates->templates->render()` method is a Plates `Template` object and as such, all Plates functions, custom functions, and extensions are accessed using the `$this` object.

There may be occasions where you want to access the Template object outside of the current template. Plates For ProcessWire makes this easy.

```php
<?php namespace ProcessWire;
  // The $this objext is now accessible outside of this Template file via the global $plate object
  $plates->exposeTemplate($this);

	$this->layout('layouts::main');
?>
<!-- ...template markup... -->
```

When `$plates->exposeTemplate($this)` is called within a template, a global `$plate` variable is created that provides access to all of the methods and properties available via the `$this` object within a Plates template. Keep in mind that this is created when ProcessWire is rendering the base template so the `$plate` variable may not be available in all contexts such as Page classes and hooks.

A good example of accessing the parent Plates template elsewhere is inside [RockPageBuilder](https://www.baumrock.com/en/processwire/modules/rockpagebuilder/) Blocks. If `$plates->exposeTemplate($this)` is used on a template that is rendering a RockPageBuilder field, then the `$plate` variable can be used inside individual block view files.

## Plates for ProcessWire Extensions

Plates for ProcessWire comes pre-packaged with extentions that you can optionally add to your project should you want to. These extensions were created specifically for this module and provide  useful tools that complement ProcessWire's objects and API. Where Plates at heart is a very lightweight templating engine, these extensions aim to add more feature parity by adding functions that act as filters and macros found in other templating engines

Each extension can be enabled when configuring the module and documentation for each is provided on the module config page. Documentation can also be viewed in individual markdown files located within `/site/modules/Plates/Extensions/Documentation/`.

## Core and Community Extensions

Plates for ProcessWire includes the ability to enable extensions provided by the Plates library on the module config page. Currently these are:

- [Asset](https://platesphp.com/extensions/asset/)
- [URI](https://platesphp.com/extensions/uri/)

To add [Community Extensions](https://platesphp.com/extensions/community/), install them with Composer and register them with Plates in your `ready.php` module.

## Syntax and Usage Tips

There are no special language constructs or changes to how you write code. No `{{ double_braces }}`  `{$braceWraps}`, just `<?=$var?>`. There's no interpreter layer, just PHP.

Is it more verbose? A little. If you're choosing a templating language because you want to avoid native shorthand PHP echo tags, then this may not be for you. If you want an _incredibly_ lightweight solution with native-feeling layouts, partials, templates, code reuse, chainable functions, and can work with [PHP's built-in alternative control structure syntax](https://www.php.net/manual/en/control-structures.alternative-syntax.php), then Plates is worth considering.

The "Syntax" page in the Plates documentation is just a few style recommendations and best practices that often apply to PHP in general. [You can view the not-really-a-syntax page here](https://platesphp.com/templates/syntax/).

Concise syntax is something that templating engines use to contrast their approach to writing templates vs standard PHP. [Smarty](https://www.smarty.net/syntax_comparison) makes this comparison between its syntax and pure PHP.  The example variable has been changed to better reflect usage with ProcessWire.

**PHP**

```php
<?php echo htmlspecialchars(strtolower($page->foo),ENT_QUOTES,'UTF-8'); ?>
```

**Smarty**

```php
{$page->foo|lower|escape}
```

**Plates**

```php
<?=$this->e($page->foo, 'strtolower')?>
```

In Plates, `e()` is the [escape](https://platesphp.com/templates/escaping/) function. As noted below, this is redundant and unnecessary in ProcessWire unless you're outputting values from an unsafe source or fields with output formatting disabled. In almost every scenario, this is safe to use:

```php
<?=strtolower($page->foo)?>
```

### A note on escaping strings

Escaping values is extremely important for safety in applications developed in a bare framework like Laravel, CakePHP, Nette, etc.

But ProcessWire isn't a bare framework, it's a _content management framework_ native to storing and outputting content safely by default. Unless you turn HTML Entitiy formatting off intentionally for fields, and usually that's done with purpose (like intentionally outputting markup/code to a page), you don't have to worry about escaping field values.

A good contrast is the [Latte](https://latte.nette.org/) templating engine which forces escaping all values and can't be globally disabled. Unless you include the `|noescape` filter, field balues will be double escaped and encoded characters that should not be present on the page may be rendered. Unless you remove the entity encoder Text formatter for each field in ProcessWire, you'll have to add this to every text variable output to the page.

```php
<title>{$page->your_field|noescape}</title>
```

With ProcessWire and Plates:

```php
<title><?=$page->your_field?></title>
```

So to re-answer the question "is it more verbose?"… maybe not after all.

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

Short tag use is not recommended as they can be disabled in any `php.ini` configuration. This is the widely accepted compatability concern and PHP documentation itself states they not be used. If short tags are disabled in any environment, shorthand echo statements will not be affected.

PHP considers `<?php ?>` and `<?= ?>` to be equally normal tags and [recommends their use exclusively](https://www.php.net/manual/en/language.basic-syntax.phptags.php).

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

//Use string interpolation to batch
// Instead of this:
<?php $fullName = trim("{$page->first_name} {$page->last_name}") ?>
<?=ucwords($fullName)?>

// Try this:
<?=$this->batch("{$page->first_name} {$page->last_name}", 'trim|ucwords')?>
```