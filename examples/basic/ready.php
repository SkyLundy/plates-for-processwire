<?php namespace ProcessWire;

if(!defined("PROCESSWIRE")) die();

/**
 * ProcessWire Bootstrap API Ready
 * ===============================
 * This ready.php file is called during ProcessWire bootstrap initialization process.
 * This occurs after the current page has been determined and the API is fully ready
 * to use, but before the current page has started rendering. This file receives a
 * copy of all ProcessWire API variables.
 *
 */

/**
 * An example of registering folders for use in Plates templates.
 *
 * Using folders is not required but is a feature that can help keep your template files organized.
 * If folders are not configured, or a Plates template is referenced without a folder, Plates will
 * look in /site/templates/
 *
 * @see https://platesphp.com/engine/folders/
 */

$templatesDir = $config->paths->templates;

// Add a folder located at /site/templates/components
$plates->templates->addFolder('components', "{$templatesDir}components");

// Add a folder located at /site/templates/layouts
$plates->templates->addFolder('layouts', "{$templatesDir}layouts");

// Add a folder located at /site/templates/views
$plates->templates->addFolder('views', "{$templatesDir}views");

/**
 * You can also use ready.php to either register your own custom functions or include a file that
 * contains function registries. These are accessed in your Plates templates via the `$this` object
 *
 * @see https://platesphp.com/engine/functions/
 */

$plates->templates->registerFunction('appendDate', function(?string $string, string $format = 'j-n-Y') {
  return "{$string} " . wire('datetime')->date($format);
});

/**
 * You can also use ready.php to add custom extensions you write yourself
 *
 * @see https://platesphp.com/engine/extensions/
 */

require_once '/path/to/your/CustomPlatesExtension.php';

$plates->templates->loadExtension(new CustomPlatesExtension());