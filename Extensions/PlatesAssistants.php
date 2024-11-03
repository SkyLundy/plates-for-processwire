<?php

/**
 * Helpful assistant methods made available to files handled by Plates
 *
 * Call using $this->{method}()
 */

declare(strict_types=1);

namespace Plates\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use ProcessWire\{WireArray, WireHttp, WireRandom, WireTextTools};

class PlatesAssistants implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        $engine->registerFunction('or', [$this, 'or']);
        $engine->registerFunction('if', [$this, 'if']);
        $engine->registerFunction('attrIf', [$this, 'attrIf']);
        $engine->registerFunction('classIf', [$this, 'classIf']);
        $engine->registerFunction('tagIf', [$this, 'tagIf']);
        $engine->registerFunction('ifTag', [$this, 'ifTag']);
        $engine->registerFunction('url', [$this, 'url']);
        $engine->registerFunction('clamp', [$this, 'clamp']);
        $engine->registerFunction('divisibleBy', [$this, 'divisibleBy']);
        $engine->registerFunction('even', [$this, 'even']);
        $engine->registerFunction('odd', [$this, 'odd']);
        $engine->registerFunction('rand', [$this, 'rand']);
        $engine->registerFunction('first', [$this, 'first']);
        $engine->registerFunction('last', [$this, 'last']);
        $engine->registerFunction('group', [$this, 'group']);
        $engine->registerFunction('slice', [$this, 'slice']);
        $engine->registerFunction('length', [$this, 'length']);
        $engine->registerFunction('wireRandom', [$this, 'wireRandom']);
        $engine->registerFunction('wireArray', [$this, 'wireArray']);
        $engine->registerFunction('wireHttp', [$this, 'wireHttp']);
        $engine->registerFunction('wireTextTools', [$this, 'wireTextTools']);
    }

    /**
     * Wire
     */

    /**
     * Assistant for instantiating a WireRandom object
     * @return WireRandom
     */
    public function wireRandom(): WireRandom
    {
        return new WireRandom();
    }

    /**
     * Assistant for creating a WireArray object
     * @return WireArray
     */
    public function wireArray(mixed ...$values): WireArray
    {
        $wireArray = new WireArray();

        if (!count($values)) {
            return $wireArray;
        }

        if (count($values) > 1) {
            return $wireArray->import($values);
        }

        return $wireArray->import($values);
    }

    /**
     * Assistant for instantiating an instance of WireHttp
     * @return WireHttp
     */
    public function wireHttp(): WireHttp
    {
        return new WireHttp();
    }

    /**
     * Assistant for instantiating an instance of WireTextTools
     * @return WireTextTools
     */
    public function wireTextTools(): WireTextTools
    {
        return new WireTextTools();
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
    public function clamp(int|float $value, int|float $min, int|float $max): int|float
    {
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
    public function divisibleBy(
        int|float|string $value,
        int|float|string $by,
    ): bool {
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
     * Arrays/Strings
     *
     * Assistants that work with either arrays or arrays and strings
     */

    /**
     * Gets the length of an array or string, nullsafe. Null returns 0
     * - Batchable
     * @param  null   $value Value to get length of
     * @return int
     */
    public function length(string|array|null $value): int
    {
        return match (true) {
            is_null($value) => 0,
            is_string($value) => strlen($value),
            default => count($value),
        };
    }

    /**
     * Returns a random value from an array or character delimited string, null safe
     * @param  string|array|WireArray  $values    Source to choose a random value from
     * @param  bool|boolean            $trim      Should trim values if string
     * @param  string                  $delimeter If $values is a string, value to split by
     * @param  mixed                   $default   Value to return if array is empty
     * @return mixed
     */
    public function rand(
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

    /**
     * Conditional Values
     */

    /**
     * Returns the first value if truthy, second if falsey
     * @param  mixed      $valueTrue  Value checked and returned if true
     * @param  mixed|null $valueFalse Value returned if first value is false, optional
     * @return mixed                  Value depending on truthiness of first argument
     */
    public function or(mixed $valueTrue, mixed $valueFalse = null): mixed
    {
        return $valueTrue ?: $valueFalse;
    }

    /**
     * Stores the conditional tag from tagIf for later return by ifTag()
     * @var null
     */
    public $ifTag = null;

    public function tagIf(mixed $conditional, string $tagTrue, string $tagFalse): string
    {
        if ($conditional) {
            $this->ifTag = $tagTrue;

            return $tagTrue;
        }

        $this->ifTag = $tagFalse;

        return $tagFalse;
    }

    public function ifTag(): ?string
    {
        return $this->ifTag;
    }

    /**
     * Returns values depending on conditional truthiness
     * @param  mixed      $conditional Value checked
     * @param  mixed|null $valueTrue   Value returned if conditional true
     * @param  mixed|null $valueFalse  Value returned if conditional false optional
     * @return mixed                   Value determined by $conditional truthiness
     */
    public function if(mixed $conditional, mixed $valueTrue = null, mixed $valueFalse = null): mixed
    {
        return $conditional ? $valueTrue : $valueFalse;
    }

    /**
     * Method to write shorter-handed conditional attributes
     * @param  mixed      $conditional Value checked
     * @param  string     $attr        Attriute to output
     * @param  string|int $valueTrue   Value returned if conditional true, optional
     * @param  string|int $valueFalse  Value returned if conditional false optional
     * @return string                  Attribute with value determined by $conditional
     */
    public function attrIf(
        mixed $conditional,
        string $attr,
        mixed $valueTrue = null,
        mixed $valueFalse = null,
    ): mixed {
        if (!$conditional && !$valueFalse) {
            return null;
        }

        if ($conditional && !$valueTrue && !$valueFalse) {
            return $attr;
        }

        if (!!$conditional && !$valueFalse) {
            return "{$attr}=\"{$valueTrue}\"";
        }

        $value = $conditional ? $valueTrue : $valueFalse;

        if ($value) {
            return "{$attr}=\"{$value}\"";
        }
    }

    /**
     * Shorthand alias for attrIf that outputs class attribute with values
     * @param  mixed      $conditional Value checked
     * @param  string|int $valueTrue   Value returned if conditional true, optional
     * @param  string|int $valueFalse  Value returned if conditional false optional
     * @return string                  Attribute with value determined by $conditional
     */
    public function classIf(
        mixed $conditional,
        mixed $valueTrue = null,
        mixed $valueFalse = null,
    ): mixed {
        return $this->attrIf($conditional, 'class', $valueTrue, $valueFalse);
    }

}