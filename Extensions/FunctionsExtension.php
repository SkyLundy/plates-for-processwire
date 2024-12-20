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
 */

declare(strict_types=1);

namespace Plates\Extensions;

use InvalidArgumentException;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Exception;
use OutOfBoundsException;
use ProcessWire\{WireArray, WireNull, WireTextTools};

use function ProcessWire\wire;

class FunctionsExtension implements ExtensionInterface
{
    public Engine $engine;

    private WireTextTools $wireTextTools;

    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        $this->engine = $engine;
        $this->wireTextTools = new WireTextTools();

        $engine->registerFunction('batchArray', [$this, 'batchArray']);
        $engine->registerFunction('bit', [$this, 'bit']);
        $engine->registerFunction('clamp', [$this, 'clamp']);
        $engine->registerFunction('csv', [$this, 'csv']);
        $engine->registerFunction('difference', [$this, 'difference']);
        $engine->registerFunction('divisibleBy', [$this, 'divisibleBy']);
        $engine->registerFunction('eq', [$this, 'eq']);
        $engine->registerFunction('even', [$this, 'even']);
        $engine->registerFunction('falseToNull', [$this, 'falseToNull']);
        $engine->registerFunction('filterWireNull', [$this, 'filterWireNull']);
        $engine->registerFunction('first', [$this, 'first']);
        $engine->registerFunction('group', [$this, 'group']);
        $engine->registerFunction('isWireArray', [$this, 'isWireArray']);
        $engine->registerFunction('jsonDecodeArray', [$this, 'jsonDecodeArray']);
        $engine->registerFunction('last', [$this, 'last']);
        $engine->registerFunction('length', [$this, 'length']);
        $engine->registerFunction('merge', [$this, 'merge']);
        $engine->registerFunction('nth', [$this, 'nth']);
        $engine->registerFunction('nth1', [$this, 'nth1']);
        $engine->registerFunction('odd', [$this, 'odd']);
        $engine->registerFunction('product', [$this, 'product']);
        $engine->registerFunction('randFrom', [$this, 'randFrom']);
        $engine->registerFunction('random', [$this, 'random']);
        $engine->registerFunction('replace', [$this, 'replace']);
        $engine->registerFunction('replaceRE', [$this, 'replaceRE']);
        $engine->registerFunction('reverse', [$this, 'reverse']);
        $engine->registerFunction('singleSpaced', [$this, 'singleSpaced']);
        $engine->registerFunction('slice', [$this, 'slice']);
        $engine->registerFunction('split', [$this, 'split']);
        $engine->registerFunction('stripHtml', [$this, 'stripHtml']);
        $engine->registerFunction('sum', [$this, 'sum']);
        $engine->registerFunction('trim', [$this, 'trim']);
        $engine->registerFunction('truncate', [$this, 'truncate']);
        $engine->registerFunction('unique', [$this, 'unique']);
        $engine->registerFunction('url', [$this, 'url']);
        $engine->registerFunction('wireGetArray', [$this, 'wireGetArray']);
    }

    /**
     * Strings
     */

    /**
     * Truncates a string, nullsafe
     * A wrapper for ProcessWire's WireTextTools::truncate() method
     *
     * @see WireTextTools::truncate()
     *
     * @param  string|null $string    String to truncate
     * @param  int         $maxLength Length to truncate to
     * @param  array       $options   Options, see ProcessWire object method description
     * @return string|null
     */
    public function truncate(?string $string, int $maxLength, array $options = []): ?string
    {
        if (!$string) {
            return $string;
        }

        return $this->wireTextTools->truncate($string, $maxLength, $options);
    }

    /**
     * Splits a string by an optional delimeter and returns an array. Null safe.
     * Returns an empty array if value is null
     *
     * - Batchable
     *
     * @param  string $value     String to split
     * @param  string $separator Value to split by
     * @return array
     */
    public function split(?string $value, ?string $separator = null): array
    {
        if (is_null($value)) {
            return [];
        }

        return match (true) {
            !!$separator => explode($separator, $value),
            default => str_split($value),
        };
    }

    /**
     * Removes all markup from a string, provides ProcessWire markup to text sanitizer, null safe.
     * Integers are ignored
     *
     * - Batchable
     *
     * @param  string|array|int|null $value     String to strip HTML from
     * @param  bool        $recursive Recursively strip HTML from strings in nested arrays
     * @return string|null
     */
    public function stripHtml(string|int|null $value = null): string|int|null
    {
        if (is_null($value)) {
            return '';
        }

        if (is_int($value)) {
            return $value;
        }

        return wire('sanitizer')->markupToText($value ?? '');
    }

    /**
     * Gets the visible length of a string excluding markup and entities, null safe
     * null value returns a length of 0
     *
     * - Batchable
     *
     * Wrapper for WireTextTools::getVisibleLength()
     *
     * @param string|null $value The value to check
     */
    public function visibleLength(?string $value): int
    {
        return $value ? $this->wireTextTools->getVisibleLength($value) : 0;
    }

    /**
     * Booleans
     */

    /**
     * Returns a 0 or 1 depending on whether the value is truthy or falsey
     *
     * - Batchable
     *
     * @param  mixed  $value A value that evaluates to true or false
     * @return int 0 or 1
     */
    public function bit(mixed $value): int
    {
        return !!$value ? 1 : 0;
    }

    /**
     * Converts a value of false to null, all other values are returned as passed if not false
     *
     * @param  mixed  $value Value to check and convert to null if false
     * @return mixed
     */
    public function falseToNull(mixed $value): mixed
    {
        return $value === false ? null : $value;
    }

    /**
     * Numbers
     */

    /**
     * Returns value clamped to the inclusive range of min and max
     *
     * @param  float     $value Value to clamp
     * @param  float     $min   Minimum value
     * @param  float     $max   Maximum value
     * @return int|float
     */
    public function clamp(
        int|float|null $value,
        int|float|null $min,
        int|float|null $max
    ): int|float|null {
        return match (true) {
            $value > $max => $max,
            $value < $min => $min,
            default => $value,
        };
    }

    /**
     * Checks if variable is divisible by a number, if strings are passed, casts to int
     *
     * @param  int  $value Number to check divisibility of
     * @param  int  $by    Number to check if divisible by
     * @return bool
     */
    public function divisibleBy(int|float|null $value, int|float|null $by): bool
    {
        if (is_null($value) || is_null($by)) {
            return false;
        }

        return $value % $by === 0;
    }

    /**
     * Checks if an integer is even
     *
     * - Batchable
     *
     * @param  int    $value Integer to check
     * @return bool
     */
    public function even(int $value): bool
    {
        return $value % 2 === 0;
    }

    /**
     * Checks if an integer is odd
     *
     * - Batchable
     *
     * @param  int    $value Integer to check
     * @return bool
     */
    public function odd(int $value): bool
    {
        return $value % 2 !== 0;
    }

    /**
     * Strings
     */

    /**
     * Ensures string is single spaced, null safe
     *
     * - Batchable
     *
     * @param  mixed  $value Value to single space
     * @return string|null
     */
    public function singleSpaced(?string $value): ?string
    {
        return is_string($value) ? preg_replace('/\s{1,}/U', ' ', $value) : $value;
    }

    /**
     * Decodes a JSON string to an array. Makes json_decode to array option batchable. All other
     * arguments are transparent
     *
     * - Batchable
     *
     * @param  string|null  $json  json_decode PHP value
     * @param  int          $depth json_decode PHP value
     * @param  int          $flags json_decode PHP value
     * @return mixed
     *
     */
    public function jsonDecodeArray(
        ?string $json,
        int $depth = 512,
        int $flags = 0
    ): mixed {
        return json_decode($json, true, $depth, $flags);
    }

    /**
     * Arrays/WireArrays
     */

    /**
     * Executes the given functions on each value in an array. Functions must be either PHP
     * functions or FunctionExtension methods that are batchable
     *
     * Emulates executing $this->batch() on every element in an array
     *
     * @param  array  $values    Array of values to execute functions on
     * @param  string $functions Pipe separated function names
     * @return array
     */
    public function batchArray(array $values, string $functions): array
    {
        $functions = explode('|', $functions);

        foreach ($functions as $function) {
            $values = array_map(function($item) use ($function) {
                if (method_exists($this, $function)) {
                    return $this->$function($item);
                }

                if (function_exists($function)) {
                    return $function($item);
                }

                return $item;
            }, $values);
        }

        return $values;
    }

    /**
     * Converts an array of values, or WireArray with specified property to a CSV string
     * Null safe
     *
     * - Batchable
     */
    public function csv(array|WireArray $values = null, ?string $property = null): string
    {
        if (is_null($values)) {
            return '';
        }

        if ($this->isWireArray($values)) {
            $values = $values->explode($property);
        }

        return implode(', ', $values);
    }


    /**
     * Converts a WireArray to an array, optionally recursive
     *
     * @param  WireArray    $wireArray WireArray object to convert
     * @param  bool|boolean $recursive Optionally execute on nested WireArray and WireArray derived objects
     * @return array
     */
    public function wireGetArray(WireArray $wireArray, bool $recursive = false): array
    {
        $array = $wireArray->getArray();

        if (!$recursive) {
            return $array;
        }

        return array_map(function($item) {
            if ($this->isWireArray($item)) {
                return $this->wireGetArray($item, true);
            }

            return $item;
        }, $array);
    }

    /**
     * Merges an arbitrary number of arrays or WireArray objects into one. Must all be of the same
     * type. Null safe.
     *
     * Invalid values that cannot be merged are ignored
     *
     * @param  array|WireArray $values Values to merge
     * @return array|WireArray
     * @throws InvalidArgumentException
     */
    public function merge(array|WireArray ...$values): array|WireArray
    {
        $values = array_filter(
            $values,
            fn ($item) => $this->isWireArray($item) || is_array($item)
        );

        $types = array_reduce($values, function($allTypes, $item) {
            $allTypes[] = gettype($item);

            return $allTypes;
        }, []);

        $types = array_unique($types);

        if (!count($types)) {
            return [];
        }

        count($types) > 1 && throw new InvalidArgumentException(
            'Cannot merge collections of different types.'
        );

        $type = $types[0];

        if ($type === 'array') {
            return array_merge(...$values);
        }

        return array_reduce(
            $values,
            fn ($wireArray, $item) => $wireArray->import($item),
            new WireArray()
        );
    }

    /**
     * Arrays/WireArrays/Strings
     *
     * Assistants that work with either arrays or arrays and strings
     */

    /**
     * Gets the length of an array or string, nullsafe. Null returns 0
     *
     * - Batchable
     *
     * @param  string|array|WireArray|null $value Value to get length of
     * @return int
     */
    public function length(string|array|WireArray|int|float|null $value): int
    {
        is_int($value) && $value = (string) $value;

        is_float($value) && $value = (string) $value;

        return match (true) {
            is_null($value) => 0,
            is_string($value) => strlen($value),
            $this->isWireArray($value) => $value->count(),
            default => count($value),
        };
    }

    /**
     * Returns a random item from an array or WireArray, random character if a string, random number
     * if integer. Returns null if value empty. Null safe
     *
     * - Batchable
     *
     * @param  WireArray|string|array|null   $value A string or arrayable item to source the random value
     * @return mixed
     */
    public function random(WireArray|string|array|int|null $value): mixed
    {
        if (!$this->length($value)) {
            return null;
        }

        if ($this->isWireArray($value)) {
            return $value->random();
        }

        $type = gettype($value);
        $value = (string) $value;

        is_string($value) && $values = str_split($value);

        $randomValue = $values[array_rand($values)];

        return $type === 'integer' ? (int) $randomValue : $randomValue;
    }

    /**
     * Removes all instances of WireNull objects and returns a new WireArray. Resets indexes
     * @param  array|WireArray $values WireArray or array to filter
     * @return array|WireArray
     */
    public function filterNull(array|WireArray $values): array|WireArray
    {
        if (is_array($values)) {
            $isList = array_is_list($values);

            $values = array_filter($values, fn ($item) => !is_null($item));

            return $isList ? array_values($values) : $values;
        }

        $values = array_filter(
            $values->getArray(),
            fn ($item) => !is_a($item, WireNull::class, true)
        );

        return WireArray::new($values);
    }

    /**
     * Returns the first character in string, number in int, character in float, item in array, or
     * item in WireArray. Null safe, empty or no items retrieved returns null.
     *
     * - Batchable
     *
     * @param  string|array|int|float|WireArray|null  $value  Value to get first item of
     * @return mixed Null if empty value passed
     */
    public function first(
        string|array|int|float|WireArray|null $values,
        bool $filterNull = false
    ): mixed {
        return $this->nth($values, 0, $filterNull);
    }

    /**
     * Returns the last character in string, number in int, character in float, item in array, or
     * item in WireArray. Null safe, empty or no items retrieved returns null
     *
     * - Batchable
     *
     * @param  string|array|int|float|WireArray|null  $value      Value to get last item of
     * @param  bool                                   $filterNull Filter null or WireNull values first
     * @return mixed
     */
    public function last(
        string|array|int|float|WireArray|null $values,
        bool $filterNull = false
    ): mixed {
        $length = $this->length($values);

        if (!$length) {
            return null;
        }

        return $this->nth($values, $length - 1, $filterNull);
    }

    /**
     * Get nth character in string, number in integer, value in float, item in array, or item in
     * WireArray. Null safe, empty or no returnable item returns null. Out of bounds returns null
     *
     * @param  string|array|int|WireArray|float|null  $value      Value to get nth item of
     * @param  int                                    $index      Index to return value from
     * @param  bool                                   $filterNull Remove null WireNull values first
     * @return mixed
     */
    public function nth(
        string|array|int|float|WireArray|null $values,
        int $index,
        bool $filterNull = false
    ): mixed {
        if ($filterNull && (is_array($values) || $this->isWireArray($values))) {
            $values = $this->filterNull($values);
        };

        $length = $this->length($values);

        if (!$length || $index > $length - 1 || $index < 0) {
            return null;
        }

        if ($this->isWireArray($values)) {
            return $values->eq($index);
        }

        $passedValueIsInt = is_int($values);
        $passedValueIsFloat = is_int($values);

        ($passedValueIsInt || $passedValueIsFloat) && $values = (string) $values;

        is_string($values) && $values = str_split($values);

        if ($index > $this->length($values) - 1) {
            return null;
        }

        $value = $values[$index];

        if (is_numeric($value)) {
            ($passedValueIsInt || $passedValueIsFloat) && $value = (int) $value;
        }

        return $value;
    }

    /**
     * Retrieve item at nth place indexed from 1
     *
     * @param  string|array|int|WireArray|null  $value      Value to get nth item of
     * @param  int                              $index      Index to return value from
     * @param  bool                             $filterNull Remove null and WireNull values first
     * @return mixed
     */
    public function nth1(
        string|array|int|WireArray|null $values,
        int $index,
        bool $filterNull = false
    ): mixed {
        $realIndex = $index - 1;

        return $this->nth($values, $realIndex, $filterNull);
    }

    /**
     * Alias for nth that matches WireArray method name
     *
     * @see FunctionsExtension::nth();
     */
    public function eq(string|array|int|float|WireArray|null $values, int $index): mixed
    {
        return $this->nth($values, $index);
    }

    /**
     * Reverses arrays, strings, ints, floats, and WireArrays, null safe
     *
     * - Batchable
     *
     * @param  mixed  $value Value to reverse
     * @return mixed
     */
    public function reverse(mixed $value = null): mixed
    {
        return match (true) {
            $this->isWireArray($value) => $value->reverse(),
            is_array($value) => array_reverse($value),
            is_string($value) => strrev($value),
            is_int($value) => (int) strrev((string) $value),
            is_float($value) => (float) strrev((string) $value),
            default => null,
        };
    }

    /**
     * Returns the value passed with only unique values. Null safe.
     *
     * - Batchable
     *
     * @param  mixed|null $value Value to return unique from
     * @return mixed
     */
    public function unique(string|int|float|array|WireArray|null $value = null): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if ($this->isWireArray($value)) {
            return $value->unique();
        }

        if (is_array($value)) {
            return array_unique($value);
        }

        $type = gettype($value);

        $value = str_split((string) $value);
        $value = array_unique($value);
        $value = implode('', $value);

        return match ($type) {
            'integer' => (int) $value,
            'double' => (float) $value,
            default => $value,
        };
    }

    /**
     * Groups an array of objects or array of arrays by a property or key, null safe
     *
     * @param  array|WireArray $values Array to group from
     * @param  string|int      $by     Key or property to group by
     * @param  bool|string     $sort   Sort by keys, true/false or 'asc'/'desc'
     * @return array
     */
    public function group(
        array|WireArray|null $values,
        string|int $by,
        bool|string $sort = false
    ): ?array {
        if (is_null($values)) {
            return null;
        }

        $isWireArray = $this->isWireArray($values);

        $isWireArray && $values = $values->getArray();

        $result = array_reduce($values, function ($grouped, $value) use ($by) {
            $key = is_array($value) ? $value[$by] : $value->$by;

            $grouped[$key] ??= [];

            $grouped[$key][] = $value;

            return $grouped;
        }, []);

        if ($sort !== false) {
            match ($sort) {
                'desc' => krsort($result),
                'asc' => ksort($result),
                default => ksort($result),
            };
        }

        // If the original object passed was a WireArray or derived object, convert all groups to
        // WireArray objects
        $isWireArray && array_walk($result, fn (&$v) => $v = (new WireArray())->import($v));

        return $result;
    }

    /**
     * Groups an array of objects or array of arrays by a property or key
     * @param  array|WireArray|string $values Array to group from
     * @param  string|int             $by     Key or property to group by
     * @return array
     */
    public function slice(
        string|array|WireArray|null $value,
        int $start,
        ?int $length = null
    ): array|WireArray|string {
        return match (true) {
            is_string($value) => $this->wireTextTools->substr($value, $start, $length),
            $this->isWireArray($value) => $value->slice($start, $length),
            default => array_slice($value, $start, $length),
        };
    }

    /**
     * Adds all of the values in an array, list array by index, associative array by key, array of
     * stdClass objects by property, or a WireArray by property. Null safe.
     *
     * Empty strings and null values are equivalent of zero.
     * Summable values may be any value that is_numeric. Integers, floats, numeric strings
     *
     * @param array|WireArray|null $values
     * @param string|int|null $property Index or property when summing arrays/objects
     */
    public function sum(array|WireArray|null $values, string|int|null $property = null): int|float
    {
        return $this->mathsOperation('sum', $values, $property);
    }

    /**
     * Subtracts all of the values in an array, list array by index, associative array by key, array of
     * stdClass objects by property, or a WireArray by property. Null safe.
     *
     * Empty strings and null values are equivalent of zero.
     * Summable values may be any value that is_numeric. Integers, floats, numeric strings
     *
     * @param array|WireArray|null $values
     * @param string|int|null $property Index or property when summing arrays/objects
     */
    public function difference(array|WireArray|null $values, string|int|null $property = null): int|float
    {
        return $this->mathsOperation('difference', $values, $property);
    }

    /**
     * Adds all of the values in an array, list array by index, associative array by key, array of
     * stdClass objects by property, or a WireArray by property. Null safe.
     *
     * Empty strings and null values are equivalent of zero.
     * Summable values may be any value that is_numeric. Integers, floats, numeric strings
     *
     * @param array|WireArray|null $values
     * @param string|int|null $property Index or property when summing arrays/objects
     */
    public function product(array|WireArray|null $values, string|int|null $property = null): int|float
    {
        return $this->mathsOperation('product', $values, $property);
    }

    /**
     * URLs
     */

    /**
     * Builds a URL with query from an array of keys and values
     * @param  array  $urlOrQuery URL if creating a full URL or query parameters if only creating a query
     * @param  array  $query      Query parameters if URL provided
     * @return string
     */
    public function url(string|array $urlOrQuery, array $query = []): string
    {
        if (is_string($urlOrQuery) && !count($query)) {
            return $urlOrQuery;
        }

        if (is_array($urlOrQuery)) {
            return http_build_query($urlOrQuery);
        }

        return "{$urlOrQuery}?" . http_build_query($query);
    }

    /**
     * Helper method to check if a value is a WireArray instance
     * @param  mixed   $value Value to check
     * @return bool
     */
    public function isWireArray(mixed $value): bool
    {
        return is_a($value, WireArray::class, true);;
    }

    /**
     * Executes a simple math operation on the provided object. Null and empty strings are counted
     * as zero
     *
     * @param string               $operation Operation to execute on the values
     * @param array|WireArray|null $values
     * @param string|int|null $property Index or property when summing arrays/objects
     * @return int|float
     */
    private function mathsOperation(
        string $operation,
        array|WireArray|null $values,
        string|int|null $property = null
    ): int|float {
        if (is_null($values)) {
            return 0;
        }

        $operator = match ($operation) {
            'sum' => '+',
            'difference' => '-',
            'product' => '*',
            default => throw new InvalidArgumentException("'{$operation}' is not a valid operation"),
        };

        if ($this->isWireArray($values)) {
            !$property && throw new Exception(
                "A property must be provided to get the {$operation} of a WireArray"
            );

            $values = $values->explode($property);
        }

        // Parses a value and attempts to get an operational integer or float or string representation
        $parseValue = function(mixed $value) use ($property, $operation): int|float|string {
            // Reject if a property is required and not provided
            is_object($value) || is_array($value) && !$property && throw new Exception(
                "A property must be provided to get the {$operation} of array or object values"
            );

            if (is_numeric($value)) {
                return $value;
            }

            $valueType = gettype($value);

            return match ($valueType) {
                'object' => $value->$property,
                'array' => $value[$property],
                'NULL' => 0,
                'string' && !$value => 0,
                default => throw new Exception("Cannot get the {$operation} of a {$valueType}"),
            };
        };

        $initialValue = 0;

        if ($operation === 'difference' || $operation === 'product') {
            $firstValue = array_shift($values);

            $initialValue = $parseValue($firstValue);
        }

        return array_reduce($values, function($total, $value) use ($parseValue, $operator) {
            $parsedValue = $parseValue($value);

            return $total = eval('return ' . "{$total}{$operator}{$parsedValue}" . ';');
        }, $initialValue);
    }
}
