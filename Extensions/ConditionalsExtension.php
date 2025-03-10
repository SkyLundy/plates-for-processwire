<?php

/**
 * Adds conditional functions to Plates template files
 */

declare(strict_types=1);

namespace PlatesForProcessWire\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use LogicException;
use ProcessWire\Page;
use ProcessWire\WireArray;
use ProcessWire\WireTextTools;

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
        $engine->registerFunction('attrIfPage', [$this, 'attrIfPage']);
        $engine->registerFunction('attrIfNotPage', [$this, 'attrIfNotPage']);
        $engine->registerFunction('fetchIf', [$this, 'fetchIf']);
        $engine->registerFunction('if', [$this, 'if']);
        $engine->registerFunction('ifEq', [$this, 'ifEq']);
        $engine->registerFunction('ifPage', [$this, 'ifPage']);
        $engine->registerFunction('ifParam', [$this, 'ifParam']);
        $engine->registerFunction('ifPath', [$this, 'ifPath']);
        $engine->registerFunction('ifTag', [$this, 'ifTag']);
        $engine->registerFunction('orTag', [$this, 'orTag']);
        $engine->registerFunction('ifUrl', [$this, 'ifUrl']);
        $engine->registerFunction('insertIf', [$this, 'insertIf']);
        $engine->registerFunction('match', [$this, 'match']);
        $engine->registerFunction('matchInt', [$this, 'matchInt']);
        $engine->registerFunction('matchStr', [$this, 'matchStr']);
        $engine->registerFunction('matchTrue', [$this, 'matchTrue']);
        $engine->registerFunction('pageIs', [$this, 'pageIs']);
        $engine->registerFunction('pageIsNot', [$this, 'pageIsNot']);
        $engine->registerFunction('paramIs', [$this, 'paramIs']);
        $engine->registerFunction('pathIs', [$this, 'pathIs']);
        $engine->registerFunction('switch', [$this, 'switch']);
        $engine->registerFunction('tagOr', [$this, 'tagOr']);
        $engine->registerFunction('tagIf', [$this, 'tagIf']);
        $engine->registerFunction('wrapIf', [$this, 'wrapIf']);
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
        bool $strict = true
    ): mixed {
        if ($strict) {
            return $this->if($if === $match, $returnTrue, null);
        }

        return $this->if($if == $match, $returnTrue, null);
    }

    /**
     * Checks if the current page matches the provided Page object, selector, or ID.
     * Returns boolean if no values passed for second and third parameters.
     * If a value is passed for one or both of the second and third parameters, returns the value
     * for true or false based on comparison. Null safe
     *
     * @param  Page|string|int|null  $pageOrSelector  Page object, selector, or page ID to check against current page
     * @param  mixed|null            $returnTrue      Optional value to return if page is current page
     * @param  mixed|null            $returnFalse     Optional value to return if page is not current page
     * @return mixed
     */
    public function ifPage(
        Page|string|int|null $pageOrSelector,
        mixed $returnTrue = true,
        mixed $returnFalse = false
    ): mixed {
        $currentPage = wire('page');

        $isCurrentPage = match (true) {
            is_string($pageOrSelector) => $currentPage->matches($pageOrSelector),
            is_int($pageOrSelector) => $currentPage->matches($pageOrSelector),
            is_a($pageOrSelector, Page::class, true) => $pageOrSelector->id === $currentPage->id,
            default => false,
        };

        return $isCurrentPage ? $returnTrue : $returnFalse;
    }

    /**
     * Checks if current page matches the provided Page object, selector, or page ID, returns a boolean, null safe
     *
     * @param  Page|string|int|null  $pageOrSelector  Page object, selector, or page ID to check against current page
     * @return bool
     */
    public function pageIs(Page|string|int|null $pageOrSelector): bool
    {
        return $this->ifPage($pageOrSelector, true, false);
    }

    /**
     * Checks if current page does not match the provided Page object, selector, or page ID, returns a boolean, null safe
     *
     * @param  Page|string|int|null  $pageOrSelector  Page object, selector, or page ID to check against current page
     * @return bool
     */
    public function pageIsNot(Page|string|int|null $pageOrSelector): bool
    {
        return !$this->pageIs($pageOrSelector);
    }

    /**
     * Checks if the given page is the current page and returns a string attribute with optional value
     *
     * @see ConditionalsExtension::attrIf()
     *
     * @param  Page|null   $page       Page to check
     * @param  string      $attr       Attribute to return
     * @param  mixed|null $valueTrue   Optional attribute value if true
     * @param  mixed|null $valueFalse  Attribute value if false
     * @return string|null
     */
    public function attrIfPage(
        Page|string|int|null $pageOrSelector,
        string $attr,
        mixed $valueTrue = null,
        mixed $valueFalse = null,
    ): ?string {
        return $this->attrIf($this->pageIs($pageOrSelector), $attr, $valueTrue, $valueFalse);
    }

    /**
     * Checks if the given page is the current page and returns a string attribute with optional value
     *
     * @see ConditionalsExtension::attrIf()
     *
     * @param  Page|null   $page       Page to check
     * @param  string      $attr       Attribute to return
     * @param  mixed|null $valueTrue   Optional attribute value if true
     * @param  mixed|null $valueFalse  Attribute value if false
     * @return string|null
     */
    public function attrIfNotPage(
        Page|string|int|null $pageOrSelector,
        string $attr,
        mixed $valueTrue = null,
        mixed $valueFalse = null,
    ): ?string {
        return $this->attrIf(!$this->pageIs($pageOrSelector), $attr, $valueTrue, $valueFalse);
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
     * Checks if the provided page path matches the current page path
     * @param  ?string $path Path to check against current path
     * @return bool
     */
    public function pathIs(?string $path): bool
    {
        return $this->ifPath($path, true, false);
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
     * Checks if a parameter exists and has the expected value
     * @param  string $urlParameter   Parameter to check for
     * @param  string $parameterValue Parameter value to check for
     * @return bool
     */
    public function paramIs(?string $urlParameter, mixed $parameterValue): bool
    {
        return $this->ifParam($urlParameter, $parameterValue);
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
        if (is_a($conditional, WireArray::class, true)) {
            $conditional = $conditional->count();
        }

        return !!$conditional ? $this->template->fetch($name, $data) : null;
    }

    /**
     * Holds the tags to return if conditional
     * Value are arrays. Key is tag, value is boolean true/false whether to render
     * @var array<array>
     */
    private array $ifTags = [];

    /**
     * Renders a tag if the given conditional is truthy
     * @param  string $tag         Tag to output
     * @param  mixed  $conditional Value to check truthyness of
     * @param  array  $attributes  Optional attributes to assign to the opening tag if output
     * @return string|null
     */
    public function tagIf(string $tag, mixed $conditional, array $attributes = []): ?string
    {
        $tag = trim($tag);

        if (!$conditional) {
            $this->ifTags[] = [$tag, false];

            return null;
        }

        $this->ifTags[] = [$tag, true];

        // Convert values to attribute strings
        array_walk($attributes, function(&$value, $attribute) {
            if (is_int($attribute)) {
                $value = $attribute;

                return;
            }

            $value = trim("{$attribute}=\"{$value}\"");
        });

        $attributes = array_values($attributes);
        $attributes = array_filter($attributes);
        $attributes = implode(' ', $attributes);

        $attributes = !!$attributes ? " {$attributes}" : '';

        return "<{$tag}{$attributes}>";
    }

    /**
     * Outputs a conditional closing tag
     * @param  string $tag Name of tag to close
     * @return string|null
     * @throws LogicException
     */
    public function ifTag(string $tag): ?string
    {
        if (empty($this->ifTags)) {
            throw new LogicException(
                'A conditional tag must be declared withh tagIf before closing with ifTag'
            );
        }

        $tag = trim($tag);

        $storedTag = array_shift($this->ifTags);

        [$closingTag, $shouldRender] = $storedTag;

        if (!$shouldRender) {
            return null;
        }

        if (strtolower($closingTag) !== strtolower($tag)) {
            throw new LogicException(
                "Invalid closing conditional tag. Expected {$closingTag} but received {$tag}"
            );
        }

        return "</{$tag}>";
    }

    /**
     * Stores the conditional tag from tagOr for later return by orTag()
     *
     * @var null|string
     */
    public string|null $orTag = null;

    /**
     * Conditional tag name rendering
     * Usage:
     *
     * ```php
     * <<?=$this->tagOr($truthy, 'h1', 'h2')?> class="something-or-other">
     *     May be an <h1> or <h2> tag depending on truthiness of first argument
     * </<?=$this->orTag()?>>
     *
     * ```
     *
     * The tagOr function can also be used to close a conditional tag when called without any
     * arguments passed
     * ```php
     * <<?=$this->tagOr($truthy, 'h1', 'h2')?> class="something-or-other">
     *     May be an <h1> or <h2> tag depending on truthiness of first argument
     * </$this->tagOr()>
     *
     * ```
     *
     * @see PlatesAssistants::orTag()
     *
     * @param  mixed  $conditional Value to checked for truthiness
     * @param  string $tagTrue     Tag if true
     * @param  string $tagFalse    Tag if false
     * @return ?string
     */
    public function tagOr(
        mixed $conditional = null,
        ?string $tagTrue = null,
        ?string $tagFalse = null,
    ): ?string {
        if (!$conditional && !$tagTrue && !$tagFalse) {
            return $this->orTag();
        }

        if (!!$conditional) {
            $this->orTag = $tagTrue;

            return $tagTrue;
        }

        $this->orTag = $tagFalse;

        return $tagFalse;
    }

    /**
     * Closing conditional tag
     * @return string|null
     */
    public function orTag(): ?string
    {
        !$this->orTag && throw new LogicException(
            'A conditional tag must be declared with tagOr before closing a conditional tag'
        );

        $tag = $this->orTag;

        $this->orTag = null;

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
     * If only a conditional value and attribute are provided, the attribute will be returned if true, null if false
     * If a truthy attribute value is passed, the attribute and value will only be returned if the conditional is true
     * If a truthy and falsey value are passed, the attribute will always be returned with the corresponding correct value
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

        if (!!$conditional && !$valueTrue && !$valueFalse) {
            return $attr;
        }

        if (!!$conditional && !$valueFalse) {
            return " {$attr}=\"{$valueTrue}\"";
        }

        $value = $conditional ? $valueTrue : $valueFalse;

        if ($value) {
            return " {$attr}=\"{$value}\"";
        }
    }

    /**
     * Inverse of attrIf
     *
     * @see ConditionalsExtension::attrIf()
     *
     * @param  mixed      $conditional Value checked
     * @param  string     $attr        Attriute to output
     * @param  string|int $valueFalse  Value returned if conditional false optional
     * @param  string|int $valueTrue   Value returned if conditional true, optional
     * @return string                  Attribute with value determined by $conditional
     */
    public function attrIfNot(
        mixed $conditional,
        string $attr,
        mixed $valueFalse = null,
        mixed $valueTrue = null,
    ): mixed {
        return $this->attrIf(!$conditional, $attr, $valueFalse, $valueTrue);
    }

    /**
     * Wraps a value in a given tag if the conditional is truthy, optional fallback tag may be
     * provided. If conditional is falsey and no fallback tag is provided, the value is returned
     * without additional markup
     *
     * @param  mixed       $conditional Value to test truthiness of
     * @param  string      $value       Value to wrap
     * @param  string      $tag         Tag to wrap if conditional is truthy
     * @param  string|null $fallbackTag Optional tag to wrap if falsey
     * @return mixed
     */
    public function wrapIf(
        mixed $conditional,
        string $value,
        string $tag,
        ?string $fallbackTag = null
    ): mixed {
        if (!$conditional && !$fallbackTag) {
            return $value;
        }

        $wireTextTools = new WireTextTools();

        $openingTag = !!$conditional ? $tag : $fallbackTag;

        return $wireTextTools->fixUnclosedTags("{$openingTag}{$value}", false);
    }
}
