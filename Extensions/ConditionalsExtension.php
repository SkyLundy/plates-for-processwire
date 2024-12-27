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

use function ProcessWire\wire;

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
        $engine->registerFunction('classIf', [$this, 'classIf']);
        $engine->registerFunction('if', [$this, 'if']);
        $engine->registerFunction('ifEq', [$this, 'ifEq']);
        $engine->registerFunction('ifPage', [$this, 'ifPage']);
        $engine->registerFunction('ifTag', [$this, 'ifTag']);
        $engine->registerFunction('ifUrl', [$this, 'ifUrl']);
        $engine->registerFunction('match', [$this, 'match']);
        $engine->registerFunction('matchTrue', [$this, 'matchTrue']);
        $engine->registerFunction('switch', [$this, 'switch']);
        $engine->registerFunction('tagIf', [$this, 'tagIf']);
    }

    /**
     * Outputs value if conditional is truthy
     *
     * @param  mixed  $conditional Value to test
     * @param  mixed  $truthyValue Value to output if truthy condition
     * @param  mixed  $falseValue  Value to output if falsey condition, null by default
     * @return mixed
     */
    public function if(
        mixed $conditional,
        mixed $truthyValue = null,
        mixed $falseValue = null
    ): mixed {
        return !!$conditional ? $truthyValue : $falseValue;
    }

    /**
     * If the first argument strict equals the second argument, return the value
     * @param  mixed  $if     Value to test
     * @param  mixed  $match  Value that triggers truth
     * @param  mixed  $value  Value to return if comparison evaluates to true
     * @param  bool   $strict Use strict comparison, default is true
     * @return mixed
     */
    public function ifEq(mixed $if, mixed $match, mixed $value, bool $strict = true): mixed
    {
        if ($strict) {
            return $this->if($if === $match, $value);
        }

        return $this->if($if == $match, $value);
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
    public function ifUrl(?string $url, mixed $returnTrue = null, mixed $returnFalse = null): mixed
    {
        $isCurrentUrl = rtrim(wire('page')->httpUrl(), '/') === rtrim($url, '/');

        if (!$returnTrue && !$returnFalse) {
            return $isCurrentUrl;
        }

        return $isCurrentUrl ? $returnTrue : $returnFalse;
    }

    /**
     * Checks if the current page matches the provided page.
     * Returns boolean if no values passed for second and third parameters.
     * If a value is passed for one or both of the second and third parameters, returns the value
     * for true or false based on comparison
     *
     * @param  Page|null  $page        Page to check if current page
     * @param  mixed|null $returnTrue  Optional value to return if page is current page
     * @param  mixed|null $returnValse Optional value to return if page is not current page
     * @return mixed
     */
    public function ifPage(?Page $page, mixed $returnTrue = null, mixed $returnFalse = null): mixed
    {
        $isCurrentPage = wire('page')->id === $page?->id;

        if (!$returnTrue && !$returnFalse) {
            return $isCurrentPage;
        }

        return $isCurrentPage ? $returnTrue : $returnFalse;
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
     * @return mixed  Returns null or
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

    /**
     * Shorthand alias for attrIf that outputs class attribute with values
     *
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
