<?php

/**
 * Helpful assistant methods made available to files handled by Plates
 *
 * Call using the following in Plates template files:
 *
 * ```php
 * <?=$this->methodName($args)?>
 * ```
 * - or -
 *
 * ```php
 * <?php if ($this->length('Firewire') > 5): ?>
 *   <h1>Hello, you have a long name.</h1>
 * <?php endif ?>
 * ```
 *
 * Methods that accept one argument may be batched alongside PHP function names:
 *
 * <?=$this->batch('Firwire', 'strtoupper|str_reverse|last')?> // Echoes 'E' to the page
 *
 * Methods that work with arrays also work with WireArray and WireArray derived classes such as
 * PageArrays
 *
 * Inspiration from:
 *  https://github.com/nette/latte
 *  https://github.com/rolandtoth/TemplateLatteReplace
 *
 */

declare(strict_types=1);

namespace PlatesForProcessWire\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use ProcessWire\Sanitizer;
use ReflectionClass;
use ReflectionMethod;

use function ProcessWire\wire;

class SanitizerExtension implements ExtensionInterface
{
    /**
     * Methods marked as pw-internal
     */
    private const INTERNAL_METHODS = [
        'getWhitespaceArray',
        'methodExists',
        'nameFilter',
        'normalizeWhitespace',
        'parseMethod',
        'reduceWhitespace',
        'removeEntities',
        'selectorField',
        'templateName',
        'username',
        'varName',
    ];

    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        $sanitizer = wire('sanitizer');

        // Extension function names are camelcase sanitizer function names prefixed with 'sanitize'
        foreach ($this->getFunctionNames() as $funcName) {
            $exFuncName = 'sanitize' . ucfirst($funcName);

            $engine->registerFunction(
                $exFuncName,
                fn (...$args) => $sanitizer->$funcName(...$args)
            );
        }
    }

    /**
     * Gets function names that will be loaded by this extension
     * @return array
     */
    private function getFunctionNames(): array
    {
        $reflection = new ReflectionClass(Sanitizer::class);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        $methods = array_filter($methods, function($method) use ($reflection) {
            if (
                $method->class !== $reflection->getName() ||
                str_starts_with($method->name, '__') ||
                in_array($method->name, self::INTERNAL_METHODS)
            ) {
                 return false;
            }

            return true;
        });

        $methodNames = array_column($methods, 'name');

        sort($methodNames);

        return $methodNames;
    }
}
