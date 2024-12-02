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

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use ProcessWire\{WireArray, WireTextTools};

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
        $engine->registerFunction('capitalize', [$this, 'capitalize']);
        $engine->registerFunction('clamp', [$this, 'clamp']);
        $engine->registerFunction('dateTime', [$this, 'dateTime']);
        $engine->registerFunction('divisibleBy', [$this, 'divisibleBy']);
        $engine->registerFunction('eq', [$this, 'eq']);
        $engine->registerFunction('even', [$this, 'even']);
        $engine->registerFunction('falseToNull', [$this, 'falseToNull']);
        $engine->registerFunction('first', [$this, 'first']);
        $engine->registerFunction('formatDate', [$this, 'formatDate']);
        $engine->registerFunction('group', [$this, 'group']);
        $engine->registerFunction('isA', [$this, 'isA']);
        $engine->registerFunction('jsonDecodeArray', [$this, 'jsonDecodeArray']);
        $engine->registerFunction('last', [$this, 'last']);
        $engine->registerFunction('length', [$this, 'length']);
        $engine->registerFunction('lower', [$this, 'lower']);
        $engine->registerFunction('nth', [$this, 'nth']);
        $engine->registerFunction('nth1', [$this, 'nth1']);
        $engine->registerFunction('odd', [$this, 'odd']);
        $engine->registerFunction('randFrom', [$this, 'randFrom']);
        $engine->registerFunction('random', [$this, 'random']);
        $engine->registerFunction('replace', [$this, 'replace']);
        $engine->registerFunction('replaceRE', [$this, 'replaceRE']);
        $engine->registerFunction('reverse', [$this, 'reverse']);
        $engine->registerFunction('slice', [$this, 'slice']);
        $engine->registerFunction('split', [$this, 'split']);
        $engine->registerFunction('stripHtml', [$this, 'stripHtml']);
        $engine->registerFunction('trim', [$this, 'trim']);
        $engine->registerFunction('truncate', [$this, 'truncate']);
        $engine->registerFunction('unique', [$this, 'unique']);
        $engine->registerFunction('upper', [$this, 'upper']);
        $engine->registerFunction('url', [$this, 'url']);
        $engine->registerFunction('wireGetArray', [$this, 'wireGetArray']);
    }

    /**
     * Dates
     */

    /**
     * Creates and returns a new DateTimeInterface object, immutable by default
     *
     * @param  string|int   $dateTime  Parseable date value
     * @param  string|null  $timezone  Optional timezone
     * @param  bool|boolean $immutable Return mutable or immutable instance
     * @return DateTimeInterface
     */
    public function dateTime(
        string|int $dateTime = 'now',
        ?string $timezone = null,
        bool $immutable = true
    ): DateTimeInterface {
        return match (true) {
            $immutable => new DateTimeImmutable($dateTime, $timezone),
            default => new DateTime($dateTime, $timezone),
        };
    }

    /**
     * Format a date string to any format recognized by PHP DateTimeImmutable, mull safe
     *
     * @param  int|string|null   $date   Date string to parse
     * @param  string $format Format to output
     * @return string|null
     */
    public function formatDate(
        int|string|null $date,
        string $format,
        ?string $timezone = null
    ): ?string {
        if (is_null($date)) {
            return null;
        }

        return $this->dateTime($date, $timezone)->format($format);
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
     * Replaces all occurrences of the search string with the replacement string. Null safe, accepts
     * ints as value replacements
     *
     * Replacing with null is equivalent to replacing with an empty string
     *
     * @param string|int|array|null $value       Value to replace strings within
     * @param string|int|array|null $search      String to find, or array of find/replace key value pairs
     * @param string|int|null       $replacement Replacement value if $search is not an array
     */
    public function replace(
        ?string $value,
        string|int|array|null $find,
        string|int|null $replace = null
    ): ?string {
        if (is_null($value) || is_null($find)) {
            return null;
        }

        if (is_array($find)) {
            foreach ($find as $findValue => $replaceValue) {
                $value = str_replace($findValue, (string) $replaceValue, $value);
            }

            return $value;
        }

        if (is_null($replace)) {
            return $value;
        }

        return str_replace($find, $replace, $value);
    }

    /**
     * Replaces values in string using RegEx pattern, null safe, accepts ints as replacements.
     *
     * Passing null for $replace is equivalent of empty string
     *
     * @param  string           $value   Value to replace
     * @param  string           $pattern RegEx pattern
     * @param  string|int|null  $replace Value to replace matches with
     * @return string|null
     */
    public function replaceRE(?string $value, string $pattern, string|int|null $replace): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return preg_replace($pattern, (string) $replace, $value);
    }

    /**
     * Converts a string lower case, ints ignored and returned
     *
     * - Batchable
     *
     * @param  string|int|null  $value Value or array of values
     * @return string|int|null
     */
    public function lower(string|array|int|null $value): string|array|null
    {
        if (is_int($value)) {
            return $value;
        }

        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Converts a string to upper case, null safe, ints ignored and returned.
     *
     * - Batchable
     *
     * @param  string|int|null  $value Value or array of values
     * @return string|int|null
     */
    public function upper(string|int|null $value): string|int|null
    {
        if (is_int($value)) {
            return $value;
        }

        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Capitalize first letter of each word in a string, null safe
     *
     * - Batchable
     *
     * @param  string|null $value Value to capitalize
     * @return string|null
     */
    public function capitalize(?string $value): ?string
    {
        return mb_convert_case((string) $value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Decodes a JSON string to an array. Makes json_decode with array option batchable
     *
     * - Batchable
     *
     * @param  string       $json         JSON String
     * @param  bool|boolean $throwOnError Throw exception on JSON decode error
     * @return array
     * @throws JsonException
     *
     */
    public function jsonDecodeArray(?string $json, bool $throwOnError = false): ?array
    {
        return $throwOnError ? json_decode($json, true, flags: JSON_THROW_ON_ERROR)
                             : json_decode($json, true);
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
                return method_exists($this, $function) ? $this->$function($item) : $function($item);
            }, $values);
        }

        return $values;
    }

    /**
     * Converts a WireArray to an array, optionally recursive
     *
     * - Batchable
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
    public function length(string|array|WireArray|null $value): int
    {
        return match (true) {
            is_null($value) => 0,
            is_string($value) => mb_strlen($value),
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
     * Returns the first character in string, number in int, item in array, or item in WireArray
     * Null safe, empty or no items retrieved returns null
     *
     * - Batchable
     *
     * @param  string|array|int|WireArray|null  $value  Value to get first item of
     * @return mixed
     */
    public function first(string|array|int|WireArray|null $values): mixed
    {
        if (!$this->length($values)) {
            return null;
        }

        if ($this->isWireArray($values)) {
            return $values->first();
        };

        is_int($values) && $values = (string) $values;
        is_string($values) && $values = str_split($values);

        return $values[0];
    }

    /**
     * Returns the first character in string, number in int, item in array, or item in WireArray
     * Null safe, empty or no items retrieved returns null
     *
     * - Batchable
     *
     * @param  string|array|int|WireArray|null  $value  Value to get last item of
     * @return mixed
     */
    public function last(string|array|int|WireArray|null $values): mixed
    {
        if (!$this->length($values)) {
            return null;
        }

        if ($this->isWireArray($values)) {
            return $values->last();
        };

        is_int($values) && $values = (string) $values;
        is_string($values) && $values = str_split($values);

        return end($values);
    }

    /**
     * Get nth character in string, number in it, item in array, or item in WireArray
     * Null safe, empty or no returnable item returns null. Out of bounds returns null
     *
     * @param  string|array|int|WireArray|null  $value  Value to get nth item of
     * @param  int                              $index  Index to return value from
     * @return mixed
     */
    public function nth(string|array|int|WireArray|null $values, int $index): mixed
    {
        $length = $this->length($values);

        if (!$length) {
            return null;
        }

        if ($this->isWireArray($values)) {
            return $values->eq($index);
        }

        is_int($values) && $values = (int) $values;
        is_string($values) && $values = str_split($values);

        //
        if ($index > count($values) - 1) {
            return null;
        }

        return $values[$index];
    }

    /**
     * Retrieve item at nth place indexed from 1
     *
     * @param  string|array|int|WireArray|null  $value  Value to get nth item of
     * @param  int                              $index  Index to return value from
     * @return mixed
     */
    public function nth1(string|array|int|WireArray|null $values, int $index): mixed
    {
        return $this->nth($values, $index - 1);
    }

    /**
     * Alias for nth that matches WireArray method
     *
     * @see FunctionsExtension::nth();
     */
    public function eq(string|array|int|WireArray|null $values, int $index): mixed
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
     * Gets the type of the value passed, optionally specify a class or expected type to check
     * against
     *
     * Accepts 'float' in place of 'double' optionally
     *
     * @param  mixed   $value       Value to get type of
     * @param  string  $classOrType Optional type to check against
     * @return string|bool
     */
    public function isA(mixed $value, ?string $classOrType = null): string|bool
    {
        if (is_object($value) && str_ends_with($classOrType ?? '', '::class')) {
            $className = str_replace('::class', '', $classOrType);

            $valueClass = get_class($value);
            $valueClass = explode('\\', $valueClass);
            $valueClass = end($valueClass);

            return $className === $valueClass;
        }

        $classOrType === 'float' && $classOrType = 'double';

        return gettype($value) === $classOrType;
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
}
