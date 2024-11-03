<?php

/**
 * Helpful assistant methods made available to files handled by Plates
 *
 * Call using $this->{method}()
 */

declare(strict_types=1);

namespace Plates\Extensions;

use Closure;
use InvalidArgumentException;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use ProcessWire\WireArray;

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
     * Checks if variable is divisible by a number
     * @param  int  $value Number to check divisibility of
     * @param  int  $by    Number to check if divisible by
     * @param  bool $cast  Whether to cast the value passed to an integer if a string is passed
     * @return bool
     * @throws InvalidArgumentException
     */
    public function divisibleBy(
        int|float|string $value,
        int|float|string $by,
        bool $cast = false
    ): bool {
        if (is_string($value) && !$cast) {
            throw new InvalidArgumentException(
                "divisibleBy expects an integer or float value argument if \$cast is not set to true, {$value} passed"
            );
        }

        if (is_string($by) && !$cast) {
            throw new InvalidArgumentException(
                "divisibleBy expects an integer or float by argument if \$cast is not set to true, {$by} passed"
            );
        }

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
     * Arrays
     */

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
        string $delimeter = ',',
        mixed $default = null,
    ): mixed {
        if (is_null($values)) {
            return $default;
        }

        is_a($values, WireArray::class, true) && $values = $values->getArray();

        is_string($values) && $values = explode($delimeter, $values);

        if ($trim) {
            $values = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $values);
        }

        return count($values) ? $values[array_rand($values)] : $default;
    }

    /**
     * Returns the first value from an array or character delimited string, null safe
     * @param  string|array|WireArray  $values    Source to choose a random value from
     * @param  bool|boolean            $trim      Should trim values if string
     * @param  string                  $delimeter If $values is a string, value to split by
     * @param  mixed                   $default   Value to return if array is empty
     * @return mixed
     */
    public function first(
        null|string|array|WireArray $values = [],
        bool $trim = true,
        string $delimeter = ',',
        mixed $default = null,
    ): mixed {
        if (is_null($values)) {
            return $default;
        }

        is_a($values, WireArray::class, true) && $values = $values->getArray();

        is_string($values) && $values = explode($delimeter, $values);

        if ($trim) {
            $values = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $values);
        }

        return count($values) ? $values[0] : $default;
    }

    /**
     * Returns the last value from an array or character delimited string, null safe
     * @param  string|array|WireArray  $values    Source to choose a random value from
     * @param  bool|boolean            $trim      Should trim values if string
     * @param  string                  $delimeter If $values is a string, value to split by
     * @param  mixed                   $default   Value to return if array is empty
     * @return mixed
     */
    public function last(
        null|string|array|WireArray $values = [],
        bool $trim = true,
        string $delimeter = ',',
        mixed $default = null,
    ): mixed {
        if (is_null($values)) {
            return $default;
        }

        is_a($values, WireArray::class, true) && $values = $values->getArray();

        is_string($values) && $values = explode($delimeter, $values);

        if ($trim) {
            $values = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $values);
        }

        return count($values) ? end($values) : $default;
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
    public function slice(array|WireArray $values, int $start, ?int $length = null): array
    {
        is_a($values, WireArray::class, true) && $values = $values->getArray();

        return array_slice($values, $start, $length);
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