<?php

/**
 * Adds conditional functions to Plates template files
 */

declare(strict_types=1);

namespace Plates\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

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
        $engine->registerFunction('attrsIf', [$this, 'attrsIf']);
        $engine->registerFunction('classIf', [$this, 'classIf']);
        $engine->registerFunction('if', [$this, 'if']);
        $engine->registerFunction('ifVal', [$this, 'ifVal']);
        $engine->registerFunction('ifTag', [$this, 'ifTag']);
        $engine->registerFunction('switch', [$this, 'switch']);
        $engine->registerFunction('tagIf', [$this, 'tagIf']);
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
     * </$this->ifTag()>
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
    public function tagIf(mixed $conditional, string $tagTrue, string $tagFalse): ?string
    {
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
        return $this->ifTag;
    }

    /**
     * Returns values depending on conditional truthiness
     * @param  mixed      $conditional Value checked
     * @param  mixed|null $valueTrue   Value returned if conditional is truthy
     * @param  mixed|null $valueFalse  Value returned if conditional is falsey, optional
     * @return mixed                   Value determined by $conditional truthiness
     */
    public function if(mixed $conditional, mixed $valueTrue = null, mixed $valueFalse = null): mixed
    {
        return !!$conditional ? $valueTrue : $valueFalse;
    }

    /**
     * Returns a value if truthy, null otherwise, simplified for use in batches
     * - Batchable
     * @param  mixed  $value Value to return if truthy
     * @return mixed
     */
    public function ifVal(mixed $value): mixed
    {
        return !!$value ?: null;
    }

    /**
     * Compares the value to an array of conditions keyed by possible value and value to output
     * where key matches
     *
     * $conditions = ['black' => 'text-black', 'white' => 'text-white'];
     *
     * @param  mixed  $value      Value to check against conditional options
     * @param  array  $conditions Array of values to output
     * @return mixed
     */
    public function switch(mixed $value, array $conditions = []): mixed
    {
        foreach ($conditions as $compare => $value) {
            if (!$value === $compare) {
                continue;
            }

            return $value;
        }
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
     * Render multiple attributes conditionally by one value
     *
     * $attrs may be:
     *
     * Attribute with truthy/falsey values
     * [
     *     'attribute-name' => ['truthy value', 'falsey value']
     * ]
     *
     * Attribute with truthy value in array
     * [
     *     'attribute-name' => ['truthy value']
     * ]
     *
     * Attribute with truthy value as value
     * [
     *     'attribute-name' => 'truthy value'
     * ]
     *
     * @param  array<string, array> $attrs Attribute sets keyed by attribute name, array of
     *                              values for truthy/falsey
     * @return string|null
     */
    public function attrsIf(mixed $conditional, array $attrs = []): ?string
    {
        array_walk($attrs, function(&$values, $attributeName) use ($conditional) {
            if (is_int($attributeName)) {
                $attributeName = $values;
                $values = [];
            }

            $values = (array) $values;

            count($values) > 2 && $values = array_slice($values, 0, 2);

            return $values = $this->attrIf($conditional, $attributeName,...$values);
        });

        $attributes = array_values($attrs);

        return implode("\n", $attributes);
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
