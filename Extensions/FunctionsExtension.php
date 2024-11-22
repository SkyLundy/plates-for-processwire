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

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use ProcessWire\WireArray;

class FunctionsExtension implements ExtensionInterface
{
    public $engine;

    public $template;

    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        $this->engine = $engine;

        $engine->registerFunction('clamp', [$this, 'clamp']);
        $engine->registerFunction('divisibleBy', [$this, 'divisibleBy']);
        $engine->registerFunction('even', [$this, 'even']);
        $engine->registerFunction('first', [$this, 'first']);
        $engine->registerFunction('group', [$this, 'group']);
        $engine->registerFunction('last', [$this, 'last']);
        $engine->registerFunction('length', [$this, 'length']);
        $engine->registerFunction('odd', [$this, 'odd']);
        $engine->registerFunction('random', [$this, 'random']);
        $engine->registerFunction('randFrom', [$this, 'randFrom']);
        $engine->registerFunction('slice', [$this, 'slice']);
        $engine->registerFunction('url', [$this, 'url']);
    }

    /**
     * Booleans
     */

    /**
     * Returns a 0 or 1 depending on whether the value is truthy or falsey
     * - Batchable
     * @param  mixed  $value A value that evaluates to true or false
     * @return int 0 or 1
     */
    public function bit(bool|string|int $value): int
    {
        return !!$value ? 1 : 0;
    }

    /**
     * Numbers
     */

    /**
     * Returns value clamped to the inclusive range of min and max
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
     * @param  int  $value Number to check divisibility of
     * @param  int  $by    Number to check if divisible by
     * @return bool
     */
    public function divisibleBy(int|float|string $value, int|float|string $by): bool
    {
        is_string($value) && $value = (int) $value;
        is_string($by) && $by = (int) $by;

        return $value % $by === 0;
    }

    /**
     * Checks if an integer is even
     * @param  int    $value Integer to check
     * @return bool
     */
    public function even(int $value): bool
    {
        return $value % 2 === 0;
    }

    /**
     * Checks if an integer is odd
     * @param  int    $value Integer to check
     * @return bool
     */
    public function odd(int $value): bool
    {
        return $value % 2 !== 0;
    }

    /**
     * Arrays/WireArrays/Strings
     *
     * Assistants that work with either arrays or arrays and strings
     */

    /**
     * Gets the length of an array or string, nullsafe. Null returns 0
     * - Batchable
     * @param  null   $value Value to get length of
     * @return int
     */
    public function length(string|array|WireArray|null $value): int
    {
        return match (true) {
            is_null($value) => 0,
            is_string($value) => strlen($value),
            is_a($value, WireArray::class, true) => $value->count(),
            default => count($value),
        };
    }

    /**
     * Returns a random item from an array or a random character if a string is passed. Returns null
     * if value is null or empty. Null safe
     * - Batchable
     *
     * @param  WireArray|string|array|null   $value A string or arrayable item to source the random value
     * @return mixed
     */
    public function random(WireArray|string|array|null $value): mixed
    {
        if (is_null($value)) {
            return null;
        }

        is_a($value, WireArray::class, true) && $value = $value->getArray();

        is_string($value) && $values = str_split($value);

        return count($values) ? $values[array_rand($values)] : null;
    }

    /**
     * Complex random function that accepts an optional delimeter if
     * Returns a random value from an array or character delimited string, null safe
     *
     * @param  string|array|WireArray  $values    Source to choose a random value from
     * @param  bool|boolean            $trim      Should trim values if string
     * @param  string                  $delimeter If $values is a string, value to split by
     * @param  mixed                   $default   Value to return if array is empty
     * @return mixed
     */
    public function randFrom(
        null|string|array|WireArray $values = [],
        bool $trim = true,
        string $delimeter = '',
        mixed $default = null,
    ): mixed {
        if (is_null($values)) {
            return $default;
        }

        is_a($values, WireArray::class, true) && $values = $values->getArray();

        if (is_string($values)) {
            $values = trim($values);

            $delimeter && $values = explode($delimeter, $values);

            !$delimeter && $values = str_split($values);
        }

        if ($trim) {
            $values = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $values);
        }

        return count($values) ? $values[array_rand($values)] : $default;
    }

    /**
     * Returns the first value from an array or character delimited string, null safe
     * - Batchable
     * @param  string|array|WireArray  $values    Source to choose a random value from
     * @param  bool|boolean            $trim      Should trim values if string
     * @param  string                  $delimeter If $values is a string, value to split by
     * @param  mixed                   $default   Value to return if array is empty
     * @return mixed
     */
    public function first(null|string|array|WireArray $values = []): mixed
    {
        if (is_null($values)) {
            return null;
        }

        is_a($values, WireArray::class, true) && $values = $values->getArray();

        is_string($values) && $values = str_split($values);

        return count($values) ? $values[0] : null;
    }

    /**
     * Returns the last value from an array or character delimited string, null safe
     * - Batchable
     * @param  string|array|WireArray  $values    Source to choose a random value from
     * @param  bool|boolean            $trim      Should trim values if string
     * @param  string                  $delimeter If $values is a string, value to split by
     * @param  mixed                   $default   Value to return if array is empty
     * @return mixed
     */
    public function last(null|string|array|WireArray $values = []): mixed
    {
        if (is_null($values)) {
            return null;
        }

        is_a($values, WireArray::class, true) && $values = $values->getArray();

        is_string($values) && $values = str_split($values);

        return count($values) ? end($values) : null;
    }

    /**
     * Groups an array of objects or array of arrays by a property or key
     * @param  array|WireArray $values Array to group from
     * @param  string|int      $by     Key or property to group by
     * @return array
     */
    public function group(array|WireArray $values, string|int $by): array
    {
        is_a($values, WireArray::class, true) && $values = $values->getArray();

        return array_reduce($values, function($grouped, $value) use ($by) {
            if (is_array($value)) {
                $grouped[$value[$by]] = $value;

                return $grouped;
            }

            $grouped[$value->$by] = $value;

            return $grouped;
        });
    }


    /**
     * Groups an array of objects or array of arrays by a property or key
     * @param  array|WireArray $values Array to group from
     * @param  string|int      $by     Key or property to group by
     * @return array
     */
    public function slice(array|WireArray $values, int $start, ?int $length = null): array|WireArray
    {
        $sliceable = $values;
        $isWireArray = is_a($sliceable, WireArray::class, true);

        $isWireArray && $sliceable = $values->getArray();

        $result = array_slice($values, $start, $length);

        if ($isWireArray) {
            $wireArray = new WireArray();
            $result = $wireArray->import($result);
        }

        return $result;
    }

    /**
     * URLs
     */

    public function url(string|array $urlOrQuery, array $query = []): string
    {
        if (is_array($urlOrQuery)) {
            return http_build_query($urlOrQuery);
        }

        return "{$urlOrQuery}?" . http_build_query($query);
    }

}
