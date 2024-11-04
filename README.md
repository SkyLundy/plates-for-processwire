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

Configurating Plates can be done in your `ready.php` file. This includes defining directories and adding extensions if desired.





