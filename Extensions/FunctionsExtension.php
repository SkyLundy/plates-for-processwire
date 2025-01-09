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

use InvalidArgumentException;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Exception;
use LogicException;
use ProcessWire\{Page, PageArray, SelectableOptionArray, WireArray, WireNull, WireTextTools};
use stdClass;
use Stringable;

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

        $engine->registerFunction('append', [$this, 'append']);
        $engine->registerFunction('batchArray', [$this, 'batchArray']);
        $engine->registerFunction('batchEach', [$this, 'batchEach']);
        $engine->registerFunction('bit', [$this, 'bit']);
        $engine->registerFunction('breadcrumbs', [$this, 'breadcrumbs']);
        $engine->registerFunction('clamp', [$this, 'clamp']);
        $engine->registerFunction('csv', [$this, 'csv']);
        $engine->registerFunction('detectVideoSvc', [$this, 'detectVideoSvc']);
        $engine->registerFunction('difference', [$this, 'difference']);
        $engine->registerFunction('divisibleBy', [$this, 'divisibleBy']);
        $engine->registerFunction('divsBy', [$this, 'divsBy']);
        $engine->registerFunction('embedUrl', [$this, 'embedUrl']);
        $engine->registerFunction('eq', [$this, 'eq']);
        $engine->registerFunction('even', [$this, 'even']);
        $engine->registerFunction('falseToNull', [$this, 'falseToNull']);
        $engine->registerFunction('filterWireNull', [$this, 'filterWireNull']);
        $engine->registerFunction('first', [$this, 'first']);
        $engine->registerFunction('flatten', [$this, 'flatten']);
        $engine->registerFunction('from1', [$this, 'from1']);
        $engine->registerFunction('group', [$this, 'group']);
        $engine->registerFunction('isWireArray', [$this, 'isWireArray']);
        $engine->registerFunction('jsonDecodeArray', [$this, 'jsonDecodeArray']);
        $engine->registerFunction('last', [$this, 'last']);
        $engine->registerFunction('length', [$this, 'length']);
        $engine->registerFunction('linksOut', [$this, 'linksOut']);
        $engine->registerFunction('merge', [$this, 'merge']);
        $engine->registerFunction('nth', [$this, 'nth']);
        $engine->registerFunction('nth1', [$this, 'nth1']);
        $engine->registerFunction('nth1End', [$this, 'nth1End']);
        $engine->registerFunction('nthEnd', [$this, 'nthEnd']);
        $engine->registerFunction('odd', [$this, 'odd']);
        $engine->registerFunction('prepend', [$this, 'prepend']);
        $engine->registerFunction('product', [$this, 'product']);
        $engine->registerFunction('random', [$this, 'random']);
        $engine->registerFunction('reverse', [$this, 'reverse']);
        $engine->registerFunction('singleSpaced', [$this, 'singleSpaced']);
        $engine->registerFunction('slice', [$this, 'slice']);
        $engine->registerFunction('stripHtml', [$this, 'stripHtml']);
        $engine->registerFunction('sum', [$this, 'sum']);
        $engine->registerFunction('toList', [$this, 'toList']);
        $engine->registerFunction('toObject', [$this, 'toObject']);
        $engine->registerFunction('truncate', [$this, 'truncate']);
        $engine->registerFunction('unique', [$this, 'unique']);
        $engine->registerFunction('url', [$this, 'url']);
        $engine->registerFunction('urlIsExternal', [$this, 'urlIsExternal']);
        $engine->registerFunction('vimeoEmbedUrl', [$this, 'vimeoEmbedUrl']);
        $engine->registerFunction('withChildren', [$this, 'withChildren']);
        $engine->registerFunction('youTubeEmbedUrl', [$this, 'youTubeEmbedUrl']);
    }

    /**
     * Strings
     */

    /**
     * Truncates a string or array of strings, safe for values that are not strings/arrays
     *
     * A wrapper for ProcessWire's WireTextTools::truncate() method
     *
     * @see WireTextTools::truncate()
     *
     * @param  mixed   $value     Values to parse/truncate
     * @param  int     $maxLength Length to truncate to
     * @param  array   $options   Options, see ProcessWire object method description
     * @return mixed
     */
    public function truncate(mixed $value, int $maxLength, array $options = []): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->truncate($item, $maxLength, $options), $value);
        }

        if (!is_string($value)) {
            return $value;
        }

        return $this->wireTextTools->truncate($value, $maxLength, $options);
    }

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
     * Removes all markup from a string or array of strings, null safe. Non-string values are
     * returned as passed
     *
     * @see WireTextTools::markupToText()
     *
     * - Batchable
     *
     * @param  string|array|int|null $value     String to strip HTML from
     * @param  bool        $recursive Recursively strip HTML from strings in nested arrays
     * @return string|null
     */
    public function stripHtml(mixed $value = null, array $options = []): mixed
    {
        $recursive = $options['recursive'] ?? false;

        if (is_array($value) && $recursive) {
            return array_map(fn ($item) => $this->stripHtml($item, $options), $value);
        }

        return is_string($value) ? wire('sanitizer')->markupToText($value, $options) : $value;
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
     * Parses a YouTube or Vimeo video URL in any format and returns a URL that can be used with an
     * video embed iframe. Returns fallback or null if a falsey value is passed or embed URL cannot
     * be created
     *
     * @param  string|null $value      URL to parse
     * @param  array       $parameters Parameters appended to the embed URL
     * @return string|null
     */
    public function embedUrl(?string $url, array $parameters = []): ?string
    {
        if (!$url) {
            return null;
        }

        $url = trim($url);
        $url = html_entity_decode($url);

        return match ($this->detectVideoSvc($url)) {
            'vimeo' => $this->vimeoEmbedUrl($url, $parameters),
            'youtube' => $this->youTubeEmbedUrl($url, $parameters),
            default => null,
        };
    }

    /**
     * Determines if a video URL is either YouTube or Vimeo, null if unable to detect or empty
     * value passed
     *
     * @param  string $url URL to analyze
     * @return string|null  'vimeo', 'youtube', or null
     */
    public function detectVideoSvc(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $url = trim($url);
        $url = html_entity_decode($url);

        return match (true) {
            str_contains($url, 'vimeo') => 'vimeo',
            str_contains($url, 'youtu') => 'youtube',
            default => null,
        };
    }

    /**
     * Parses a Vimeo video URL and returns an iframe embed-ready URL. Returns null if URL passed is
     * falsey or if URL is not a vimeo URL
     *
     * @param  string|null $url       Vimeo URL
     * @param  array       $parameters Parameters appended to the embed URL
     * @return string|null
     */
    public function vimeoEmbedUrl(?string $url, array $parameters = []): ?string
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $url = html_entity_decode($url);

        $urlComponents = parse_url($url);

        $urlPath = $urlComponents['path'] ?? '';

        if (!$urlPath) {
            return null;
        }

        $videoId = preg_replace('/[^0-9]/', '', $urlPath);

        if (!$videoId) {
            return null;
        }

        $parameters = [
            'badge' => 0,
            'autopause' => 0,
            'player_id' => 0,
            ...$parameters,
        ];

        $parameters = array_filter($parameters, fn ($value) => !is_null($value));

        return $this->url("https://player.vimeo.com/video/{$videoId}", $parameters);
    }

    /**
     * Parses a YouTube video URL and returns an iframe embed-ready URL. Returns null if URL passed is
     * falsey or if URL is not a vimeo URL
     *
     * Regex source: https://stackoverflow.com/questions/3392993/php-regex-to-get-youtube-video-id
     *
     * @param  string|null $url        YouTube URL
     * @param  array       $parameters Parameters appended to the embed URL
     * @return string|null
     */
    public function youTubeEmbedUrl(?string $url, array $parameters = []): ?string
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $url = html_entity_decode($url);

        preg_match(
            '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|live/)|youtu\.be/)([^"&?/ ]{11})%',
            $url,
            $matches
        );

        if (count($matches) !== 2) {
            return null;
        }

        $videoId = end($matches);

        $parameters = [
            'playsinline=1',
            ...$parameters,
        ];

        $parameters = array_filter($parameters, fn ($value) => !is_null($value));

        return $this->url("https://www.youtube.com/embed/{$videoId}", $parameters);
    }

    /**
     * Checks whether the given URL is internal (same domain) or external (other domain)
     *
     * Returns null if no URL provided or internal/external state cannot be determined
     *
     * @param string|bool $url         URL to check
     * @param mixed       $returnTrue  Value to return if URL is external
     * @param mixed       $returnFalse Value to return if URL is internal
     */
    public function urlIsExternal(
        ?string $url = null,
        mixed $returnTrue = true,
        mixed $returnFalse = false,
    ): mixed {
        if (!$url) {
            return null;
        }

        $url = preg_replace('/https?:\/\//', '', $url);

        return !str_starts_with($url, wire('config')->httpHost) ? $returnTrue : $returnFalse;
    }

    /**
     * Alias for linkIsExternal()
     *
     * @see ConditionalsExtension::linkIsExternal()
     */
    public function linksOut(
        ?string $url,
        mixed $returnTrue = true,
        mixed $returnFalse = false
    ): mixed {
        return $this->urlIsExternal($url, $returnTrue, $returnFalse);
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
     * Alias for divisibleBy()
     *
     * @see FunctionsExtension::divisibleBy()
     */
    public function divsBy(int|float|null $value, int|float|null $by): bool
    {
        return $this->divisibleBy($value, $by);
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
    public function batchEach(array $values, string $functions): array
    {
        $functions = explode('|', $functions);

        // Lovingly borrowed from Plates core
        // @see League\Plates\Template::batch()
        $batch = function($var) use ($functions): mixed {
            foreach ($functions as $function) {
                if ($this->engine->doesFunctionExist($function)) {
                    $var = call_user_func(array($this, $function), $var);
                } elseif (is_callable($function)) {
                    $var = call_user_func($function, $var);
                } else {
                    throw new LogicException(
                        'The batch function could not find the "' . $function . '" function.'
                    );
                }
            }

            return $var;
        };

        return array_map(fn ($item) => $batch($item), $values);
    }

    /**
     * Alias for batchEach()
     *
     * @see FunctionsExtension::batchEach()
     */
    public function batchArray(array $values, string $functions): array
    {
        return $this->batchEach($values, $functions);
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
     * Re-indexes an iterable from 1
     *
     * @param  iterable $iterable Array or WireArray
     * @return array
     */
    public function from1(iterable $iterable): array
    {
        $indexes = range(1, $this->length($iterable));

        if ($this->isWireArray($iterable)) {
            $iterable = $iterable->getArray();
        }

        $values = array_values($iterable);

        return array_combine($indexes, $values);
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
     * Converts associative arrays to stdClass objects recursively. Changes method of accessing
     * values from an array [] notation to a fluent -> notation as stdClass objects
     *
     * List arrays are not converted to objects
     *
     * @param  array  $value List array or mutlidimensional array
     * @return array|stdClass
     */
    public function toObject(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $array = array_map(function($item) {
            if (!is_array($item)) {
                return $item;
            }

            return $this->toObject($item);
        }, $value);

        return array_is_list($array) ? $array : (object) $array;
    }

    /**
     * Converts an iterable value to an index array. Associative arrays are returned indexed without keys.
     * @param  iterable $value Array, WireArray, or WireArray derived object
     * @return array
     */
    public function toList(iterable $value): array
    {
        if ($this->isWireArray($value)) {
            return array_values($value->getArray());
        }

        return array_values($value);
    }

    /**
     * Given a parent page or parent page selector, gets children, prepends parent page, and returns
     * a new PageArray. Optional child page selector may be provided to select/filter children
     *
     * @param  Page|string $parentPageOrSelector Parent page or parent page selector
     * @param  string|null $childSelector        Optional child page selector
     * @return PageArray
     */
    public function withChildren(
        Page|int|string $parentPageOrSelector,
        ?string $childSelector = null
    ): PageArray {
        $parentPage = match (true) {
            is_a($parentPageOrSelector, Page::class, true) => $parentPageOrSelector,
            default => wire('pages')->get($parentPageOrSelector),
        };

        return $parentPage->children($childSelector ?? '')->prepend($parentPage);
    }

    /**
     * Arrays/WireArrays/Strings
     *
     * Assistants that work with multiple value types
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
     * Returns a random item from a string, integer, float,  array or WireArray, random character if
     * a string, random number if integer. Returns null if value empty. Null safe
     *
     * - Batchable
     *
     * @param  WireArray|string|int|float|array|null $value Source the random value
     * @param  bool                                  $filterNulls
     * @return mixed
     */
    public function random(
        WireArray|string|array|int|float|null $values,
        bool $filterNulls = false,
    ): mixed {
        if ($filterNulls && (is_array($values) || $this->isWireArray($values))) {
            $values = $this->filterNull($values);
        }

        $length = $this->length($values);

        if (!$length) {
            return null;
        }

        return $this->nth($values, rand(0, $length - 1), $filterNulls);
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
        $passedValueIsFloat = is_float($values);

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
        string|array|int|float|WireArray|null $values,
        int $index,
        bool $filterNull = false
    ): mixed {
        return $this->nth($values, $index - 1, $filterNull);
    }

    /**
     * Retrieve item at nth place from the end indexed from 0
     *
     * @param  string|array|int|WireArray|null  $value      Value to get nth item of
     * @param  int                              $index      Index from the end to return value from
     * @param  bool                             $filterNull Remove null and WireNull values first
     * @return mixed
     */
    public function nthEnd(
        string|array|int|float|WireArray|null $values,
        int $index,
        bool $filterNull = false
    ): mixed {
        return $this->nth($this->reverse($values), $index, $filterNull);
    }

    /**
     * Retrieve item at nth place from the end indexed from 1
     *
     * @param  string|array|int|WireArray|null  $value      Value to get nth item of
     * @param  int                              $index      Index from the end to return value from
     * @param  bool                             $filterNull Remove null and WireNull values first
     * @return mixed
     */
    public function nth1End(
        string|array|int|float|WireArray|null $values,
        int $index,
        bool $filterNull = false
    ): mixed {
        return $this->nth($this->reverse($values), $index - 1, $filterNull);
    }

    /**
     * Equals. Compares first value to second value, returns boolean. Weak comparison by default,
     * strict comparison optional
     *
     * @param mixed $value1 First value to compare
     * @param mixed $value2 Second value to compare
     * @param bool  $strict Compare values using strict operator
     * @return bool
     */
    public function eq(mixed $value1, mixed $value2, bool $strict = false): bool
    {
        return $strict ? $value1 === $value2 : $value1 == $value2;
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
            $unique = array_unique($value);

            return array_values($unique);
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
     * When grouping by a field, the value of the field must be a valid array key type or
     * compatible field. Fields that return a page are grouped by ID. SelectableOption fields are
     * grouped by label.
     *
     * When grouping by raw text, values are case sensitive
     *
     * For valid key types and automatic value casting by PHP
     * @see https://www.php.net/manual/en/language.types.array.php
     *
     * @param  array|WireArray $values  Array to group from
     * @param  string|int      $groupBy Key or property to group by
     * @param  bool|string     $sort    Sort by keys, true/false or 'asc'/'desc'
     * @return array
     */
    public function group(
        array|WireArray|null $values,
        string|int $groupBy,
        bool|string $sort = false,
    ): ?array {
        if (is_null($values)) {
            return null;
        }

        $isWireArray = $this->isWireArray($values);

        $isWireArray && $values = $values->getArray();

        // Checks if a found value can be used as an array key
        $keyValid = fn ($key) => match(gettype($key)) {
            'object' => false,
            'array' => false,
            default => true,
        };

        $result = array_reduce($values, function ($grouped, $value) use ($groupBy, $keyValid) {
            $by = $groupBy;

            // Get a value to use as a key
            $key = match (true) {
                is_a($value->$by, SelectableOptionArray::class, true) => $value->$by->get("{$value->$by}")?->title,
                is_a($value->$by, Page::class, true) => $value->$by->id,
                is_array($value) => $value[$by],
                default => $value->$by,
            };

            // If the field returns a page, use the ID
            if (is_a($value->$by, Page::class, true)) {
                $key = $key->id;
            }

            if (!$keyValid($key)) {
                $keyType = get_class($key);

                throw new LogicException("Cannot group by '{$groupBy}', invalid field '{$keyType}'");
            }

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
        string|array|int|float|WireArray|null $value,
        int $start,
        ?int $length = null
    ): array|WireArray|int|float|string|null {
        if (is_null($value)) {
            return null;
        }

        $type = gettype($value);

        ($type === 'double' || $type === 'integer') && $value = (string) $value;

        $result = match (true) {
            is_string($value) => $this->wireTextTools->substr($value, $start, $length),
            $this->isWireArray($value) => $value->slice($start, $length),
            default => array_slice($value, $start, $length),
        };

        return $result;
    }

    /**
     * Flattens a multidimensional array, WireArray, or WireArray derived object, null safe. Null
     * returns empty array
     *
     * @param  array|WireArray|null  $array Array or WireArray to flatten
     * @return array
     */
    public function flatten(array|WireArray|null $array): array|WireArray
    {
        if (is_null($array)) {
            return [];
        }

        $value = $array;

        $isWireArray = $this->isWireArray($array);

        $isWireArray && $value = $array->getArray();

        $result = array_reduce($value, function($out, $item) {
            if (is_array($item) || $this->isWireArray($item)) {
                $item = $this->flatten($item);
            }

            return is_array($item) ? [...$out, ...$item] : [...$out, $item];
        }, []);

        return $isWireArray ? (new WireArray())->import($result) : $result;
    }

    /**
     * Append a value to a string, int, array, or WireArray. Null safe,
     * @return mixed
     */
    public function append(mixed $appendTo, mixed ...$values): mixed
    {
        if (is_null($appendTo)) {
            return null;
        }

        if ($this->isWireArray($appendTo)) {
            return $appendTo->add(...$values);
        }

        $appendToType = gettype($appendTo);

        return match ($appendToType) {
            'array' => $this->appendArray($appendTo, $values),
            'string' => $this->appendString($appendTo, $values),
            'integer' => $this->appendInt($appendTo, $values),
            default => throw new LogicException("Cannot prepend value to item of type {$appendToType}"),
        };
    }

    /**
     * Append a value to a string, int, array, or WireArray. Null safe
     * @return mixed
     */
    public function prepend(mixed $prependTo, mixed ...$values): mixed
    {
        if (is_null($prependTo)) {
            return null;
        }

        if ($this->isWireArray($prependTo)) {
            return $prependTo->prepend(...$values);
        }

        $reversed = $this->reverse($prependTo);

        $appended = $this->append($reversed, $values);

        return $this->reverse($appended);
    }

    /**
     * Markup
     */

    /**
     * Renders a list of breadcrumb links
     * @param  array  $config Options for rendering]
     * @return string
     */
    public function breadcrumbs(array $config = []): string
    {
        $config = (object) [
            'startPage' => '/',
            'labelField' => 'title',
            'prependHome' => true,
            'appendCurrent' => false,
            'currentAsLink' => false,
            'ulId' => null,
            'ulClass' => null,
            'separator' => null,
            ...$config,
        ];

        $startPage = $config->startPage;

        if (!is_a($startPage, Page::class, true)) {
            $startPage = wire('pages')->get($config->startPage);
        }

        $breadcrumbPages = wire('page')->parents;

        if (!$config->prependHome) {
            $home = wire('pages')->get('/');

            $breadcrumbPages->remove($home);
        }

        if ($config->appendCurrent) {
            $breadcrumbPages->append(wire('page'));
        }

        $items = array_map(function($page) use ($config) {
            $label = $page->{$config->labelField};
            $itemContent = $label;

            if ($page->id !== wire('page')->id || $config->currentAsLink) {
                $itemContent = "<a href='{$page->url}'>{$label}</a>";
            }

            return "<li>{$itemContent}</li>";
        }, $breadcrumbPages->getArray());

        $items = implode($config->separator ? "<li>{$config->separator}</li>" : '', $items);

        $ulAttrs = [];

        $config->ulId && $ulAttrs[] = "id='{$config->ulId}'";
        $config->ulClass && $ulAttrs[] = "class='{$config->ulClass}'";

        $ulAttrs = implode(' ', $ulAttrs);

        $ulAttrs && $ulAttrs = " {$ulAttrs}";

        return <<<HTML
        <ul{$ulAttrs}>
            {$items}
        </ul>
        HTML;
    }

    /**
     * Calculations
     */

    /**
     * Adds all of the values in an array, list array by index, associative array by key, array of
     * stdClass objects by property, or a WireArray by property. Null safe.
     *
     * Empty strings and null values are equivalent of zero.
     * Summable values may be any value that is_numeric. Integers, floats, numeric strings
     *
     * @param array|WireArray|null $values
     * @param string`|int|null $property Index or property when summing arrays/objects
     */
    public function sum(
        array|WireArray|null $values,
        string|int|null $property = null
    ): int|float {
        return $this->mathsOperation('sum', $values, $property);
    }

    /**
     * Subtracts all of the values in an array, list array by index, associative array by key, array
     * of stdClass objects by property, or a WireArray by property. Null safe.
     *
     * Empty strings and null values are equivalent of zero.
     * Summable values may be any value that is_numeric. Integers, floats, numeric strings
     *
     * @param array|WireArray|null $values
     * @param string|int|null $property Index or property when summing arrays/objects
     */
    public function difference(
        array|WireArray|null $values,
        string|int|null $property = null
    ): int|float {
        return $this->mathsOperation('difference', $values, $property);
    }

    /**
     * Multiplies all of the values in an array, list array by index, associative array by key,
     * or a WireArray by property. Null safe.
     *
     * Empty strings and null values are equivalent of zero.
     * Summable values may be any value that is_numeric. Integers, floats, numeric strings
     *
     * @param array|WireArray|null $values
     * @param string|int|null $property Index or property when summing arrays/objects
     */
    public function product(
        array|stdClass|WireArray|null $values,
        string|int|null $property = null
    ): int|float {
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
     * Misc
     */

    /**
     * Helper method to check if a value is a WireArray instance
     * @param  mixed   $value Value to check
     * @return bool
     */
    public function isWireArray(mixed $value): bool
    {
        return is_a($value, WireArray::class, true);
    }

    /**
     * Internals
     */

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
        array|stdClass|WireArray|null $values,
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

            if ($valueType === 'object' && !property_exists($value, $property)) {
                throw new LogicException(
                    "Unable to get {$operation}. Property '{$property}' does not exist"
                );
            }

            if ($valueType === 'array' && !array_key_exists($property, $value)) {
                throw new LogicException(
                    "Unable to get {$operation}. Array key '{$property}' does not exist"
                );
            }

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


    /**
     * Append helper methods
     */

    private function appendArray(array $appendTo, mixed ...$values): array
    {
        return array_reduce($values[0], function($out, $value) {
            return $out = is_array($value) ? [...$out, ...$value] : [...$out, $value];
        }, $appendTo);
    }

    private function appendString(string $appendTo, mixed ...$values): string
    {
        return array_reduce($values[0], function($out, $value) {
            if (
                is_string($value) ||
                is_int($value) ||
                is_float($value)
            ) {
                return $out . (string) $value;
            }

            if (is_array($value)) {
                return $out = $this->appendString($out, $value);
            }

            if (!$value instanceof Stringable) {
                $valueType = gettype($value);

                throw new LogicException("Cannot append a value of type {$valueType} to a string");
            }

            return $out . (string) $value;
        }, $appendTo);
    }

    private function appendInt(int $appendTo, mixed ...$values): int
    {
        return array_reduce($values[0], function($out, $value) {
            if (is_array($value)) {
                return $out = $this->appendInt($out, $value);
            }

            $castValue = (int) $value;

            return (int) "{$out}{$castValue}";
        }, $appendTo);
    }
}
