<?php

/**
 * Adds conditional functions to Plates template files
 */

declare(strict_types=1);

namespace Plates\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use LogicException;
use ProcessWire\Page;
use ProcessWire\WireArray;

use function ProcessWire\wire;
use function ProcessWire\WireArray;

class ConditionalsExtension implements ExtensionInterface
{
    public $engine;

    public $template;

    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        $this->engine = $engine;

        $engine->registerFunction('attrIf', [$this, 'attrIf']);
        $engine->registerFunction('fetchIf', [$this, 'fetchIf']);
        $engine->registerFunction('if', [$this, 'if']);
        $engine->registerFunction('ifEq', [$this, 'ifEq']);
        $engine->registerFunction('ifPage', [$this, 'ifPage']);
        $engine->registerFunction('ifParam', [$this, 'ifParam']);
        $engine->registerFunction('ifPath', [$this, 'ifPath']);
        $engine->registerFunction('ifTag', [$this, 'ifTag']);
        $engine->registerFunction('ifUrl', [$this, 'ifUrl']);
        $engine->registerFunction('insertIf', [$this, 'insertIf']);
        $engine->registerFunction('match', [$this, 'match']);
        $engine->registerFunction('matchInt', [$this, 'matchInt']);
        $engine->registerFunction('matchStr', [$this, 'matchStr']);
        $engine->registerFunction('matchTrue', [$this, 'matchTrue']);
        $engine->registerFunction('switch', [$this, 'switch']);
        $engine->registerFunction('tagIf', [$this, 'tagIf']);
    }

    /**
     * Outputs value if conditional is truthy
     *
     * @param  mixed  $conditional Value to test
     * @param  mixed  $returnTrue  Value to output if truthy condition
     * @param  mixed  $returnFalse Value to output if falsey condition
     * @return mixed
     */
    public function if(
        mixed $conditional,
        mixed $returnTrue = null,
        mixed $returnFalse = null
    ): mixed {
        return !!$conditional ? $returnTrue : $returnFalse;
    }

    /**
     * If the first argument strict equals the second argument, return the value
     *
     * @param  mixed  $if          Value to test
     * @param  mixed  $match       Value that triggers truth
     * @param  mixed  $returnTrue  Value to return if comparison evaluates to true
     * @param  mixed  $returnFalse Value to return if comparison evaluates to true
     * @param  bool   $strict      Use strict comparison, default is true
     * @return mixed
     */
    public function ifEq(
        mixed $if,
        mixed $match,
        mixed $returnTrue = true,
        mixed $returnFalse = false,
        bool $strict = true
    ): mixed {
        if ($strict) {
            return $this->if($if === $match, $returnTrue, $returnFalse);
        }

        return $this->if($if == $match, $returnTrue, $returnFalse);
    }

    /**
     * Checks if the current page matches the provided page.
     * Returns boolean if no values passed for second and third parameters.
     * If a value is passed for one or both of the second and third parameters, returns the value
     * for true or false based on comparison
     *
     * @param  Page|null  $page        Page to check if current page
     * @param  mixed|null $returnTrue  Optional value to return if page is current page
     * @param  mixed|null $returnFalse Optional value to return if page is not current page
     * @return mixed
     */
    public function ifPage(?Page $page, mixed $returnTrue = true, mixed $returnFalse = false): mixed
    {
        $isCurrentPage = wire('page')->id === $page?->id;

        return $isCurrentPage ? $returnTrue : $returnFalse;
    }

    /**
     * Checks if the current page path the provided path.
     * Returns boolean if no values passed for second and third parameters.
     * If a value is passed for one or both of the second and third parameters, returns the value
     * for true or false based on comparison
     *
     * @param  Page|null  $page        Page to check if current page
     * @param  mixed|null $returnTrue  Optional value to return if page is current page
     * @param  mixed|null $returnFalse Optional value to return if page is not current page
     * @return mixed
     */
    public function ifPath(
        ?string $path,
        mixed $returnTrue = true,
        mixed $returnFalse = false
    ): mixed {
        if (!$path) {
            return $returnFalse;
        }

        // Remove all GET parameters
        // Remove leading/trailing slashes
        $cleanPath = function(string $pathStr) {
            $pathStr = preg_replace('/\?.{1,}/', '', $pathStr);

            return trim($pathStr, '/');
        };

        return $cleanPath($path) === $cleanPath(wire('page')->path) ? $returnTrue : $returnFalse;
    }

    /**
     * Checks if the provided GET parameter exists in the URL and has the expected value. Passing a
     * boolean second argument will check if the parameter does or does not exist. Third and forth
     * arguments are values returned if check passes or fails respectively
     *
     * @param  string|null      $urlParameter    The name of the parameter to check for
     * @param  string|bool|null $parameterValue  The value to compare, or boolean for parameter presence
     * @return mixed
     */
    public function ifParam(
        ?string $urlParameter,
        string|bool|null $parameterValue = null,
        mixed $returnTrue = true,
        mixed $returnFalse = false,
    ): mixed {
        $urlParamValue = wire('input')->get($urlParameter);

        return match ($parameterValue) {
            null => !!$urlParamValue ? $returnTrue : $returnFalse,
            true => !!$urlParamValue ? $returnTrue : $returnFalse,
            false => !$urlParamValue ? $returnTrue : $returnFalse,
            default => $parameterValue == $urlParamValue ? $returnTrue : $returnFalse,
        };
    }

    /**
     * Checks the given URL against the current URL. Returns boolean true/false if no second or
     * third parameters are provided. Ignores GET variables and trailing slashes
     *
     * @param  string     $url         URL to check against current page URL
     * @param  mixed|null $returnTrue  Optional value to return if URL matches current page
     * @param  mixed|null $returnFalse Optional value to return if URL does not match current page
     * @return mixed
     */
    public function ifUrl(?string $url, mixed $returnTrue = true, mixed $returnFalse = false): mixed
    {
        // Remove all GET parameters
        // Remove leading/trailing slashes
        $cleanUrl = function(string $pathStr) {
            $pathStr = preg_replace('/\?.{1,}/', '', $pathStr);

            return trim($pathStr, '/');
        };

        return $cleanUrl($url) === $cleanUrl(wire('page')->httpUrl()) ? $returnTrue : $returnFalse;
    }

    /**
     * A conditional for inserting Plates templates
     *
     * @see fetchIf()
     *
     * @param  string $name        Name of template
     * @param  mixed  $conditional Value to test for truthy/falsey value
     * @param  array  $data        Optional data passed to the template
     * @return void
     */
    public function insertIf(string $name, mixed $conditional = null, array $data = []): void
    {
        $isWireArray = is_a($conditional, WireArray::class, true);

        if ($isWireArray && !$conditional->count()) {
            return;
        }

        if (!!$conditional) {
            $this->template->insert($name, $data);
        }
    }

    /**
     * A conditional for fetching Plates templates
     *
     * @see insertIf()
     *
     * @param  string $name        Name of template
     * @param  mixed  $conditional Value to test for truthy/falsey value
     * @param  array  $data        Optional data passed to the template
     * @return void
     */
    public function fetchIf(string $name, mixed $conditional = null, array $data = []): ?string
    {
        $isWireArray = is_a($conditional, WireArray::class, true);

        if ($isWireArray && !$conditional->count()) {
            return null;
        }

        if (!!$conditional) {
            return $this->template->fetch($name, $data);
        }
    }

    /**
     * Stores the conditional tag from tagIf for later return by ifTag()
     *
     * @var null|string
     */
    public string|null $ifTag = null;

    /**
     * Conditional tag name rendering
     * Usage:
     *
     * ```php
     * <<?=$this->tagIf($truthy, 'h1', 'h2')?> class="something-or-other">
     *     May be an <h1> or <h2> tag depending on truthiness of first argument
     * </<?=$this->ifTag()?>>
     *
     * ```
     *
     * The tagIf function can also be used to close a conditional tag when called without any
     * arguments passed
     * ```php
     * <<?=$this->tagIf($truthy, 'h1', 'h2')?> class="something-or-other">
     *     May be an <h1> or <h2> tag depending on truthiness of first argument
     * </$this->tagIf()>
     *
     * ```
     *
     * @see PlatesAssistants::ifTag()
     *
     * @param  mixed  $conditional Value to checked for truthiness
     * @param  string $tagTrue     Tag if true
     * @param  string $tagFalse    Tag if false
     * @return ?string
     */
    public function tagIf(
        mixed $conditional = null,
        ?string $tagTrue = null,
        ?string $tagFalse = null,
    ): ?string {
        if (!$conditional && !$tagTrue && !$tagFalse) {
            return $this->ifTag();
        }

        if (!!$conditional) {
            $this->ifTag = $tagTrue;

            return $tagTrue;
        }

            $this->ifTag = $tagFalse;

            return $tagFalse;
    }

    /**
     * Closing conditional tag
     * @return string|null
     */
    public function ifTag(): ?string
    {
        !$this->ifTag && throw new LogicException(
            'A conditional tag must be declared with tagIf before closing a conditional tag'
        );

        $tag = $this->ifTag;

        $this->ifTag = null;

        return $tag;
    }

    /**
     * Compares the value to an array of cases keyed by possible value and value to output if
     * match is found
     *
     * $cases = ['black' => 'text-black', 'white' => 'text-white'];
     *
     * @param  mixed  $value      Value to check against conditional options
     * @param  array  $cases Array of values to output
     * @param  mixed  $default    Default value if all cases fail
     * @return mixed
     */
    public function match(mixed $conditional, array $cases = [], mixed $default = null): mixed
    {
        foreach ($cases as $candidate => $value) {
            if ($conditional === $candidate) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Executes match() with first argument cast to an integer
     *
     * @see ConditionalsExtension::match()
     *
     * @return mixed
     */
    public function matchInt(mixed $conditional, array $cases = [], $default = null): mixed
    {
        return $this->match((int) $conditional, $cases, $default);
    }

    /**
     * Executes ConditionalsExtension::match() with first argument cast to a string
     *
     * @see ConditionalsExtension::match()
     *
     * @return mixed
     */
    public function matchStr(mixed $conditional, array $cases = [], mixed $default = null): mixed
    {
        return $this->match((string) $conditional, $cases, $default);
    }

    /**
     * Returns the first key where the value is truthy
     *
     * $cases = ['text-black' => $color === 'black', 'text-white' => $color === 'white'];
     *
     * @param  mixed  $cases   Value/truthy sets
     * @param  mixed  $default Default value returned if no cases are truthy
     * @return mixed  Value of $default, null if value not passed
     */
    public function matchTrue(array $cases = [], mixed $default = null): mixed
    {
        foreach ($cases as $value => $conditional) {
            if (!!$conditional) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Alias for ConditionalExtension::match()
     *
     * @see ConditionalExtension::match()
     */
    public function switch(mixed $conditional, array $cases = [], mixed $default = null): mixed
    {
        return $this->match($conditional, $cases, $default);
    }

    /**
     * Method to write shorter-handed conditional attributes. Output value is prefixed with a space
     *
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
            return " {$attr}=\"{$value}\"";
        }
    }
}
