<?php

/**
 * Extension that makes Wire utility objects available via callable functions in Plates templates
 */

declare(strict_types=1);

namespace Plates\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use ProcessWire\{WireArray, WireHttp, WireNumberTools, WireRandom, WireTextTools};

class WireExtension implements ExtensionInterface
{
    private ?WireRandom $wireRandom = null;

    private ?WireTextTools $wireTextTools = null;

    private ?WireNumberTools $wireNumberTools = null;

    public $engine;

    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        $this->engine = $engine;

        $engine->registerFunction('wireArray', [$this, 'wireArray']);
        $engine->registerFunction('wireHttp', [$this, 'wireHttp']);
        $engine->registerFunction('wireRandom', [$this, 'wireRandom']);
        $engine->registerFunction('wireTextTools', [$this, 'wireTextTools']);
        $engine->registerFunction('wireNumberTools', [$this, 'wireNumberTools']);
    }

    /**
     * Assistant for instantiating a WireRandom object
     * @return WireRandom
     */
    public function wireRandom(?string $method = null): mixed
    {
        $this->wireRandom = $this->wireRandom ??  new WireRandom();

        return $method ? $this->wireRandom->{$method}() : $this->wireRandom;
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
        if ($this->wireTextTools) {
            return $this->wireTextTools;
        }

        return $this->wireTextTools = new WireTextTools();
    }

    /**
     * Assistant for instantiating an instance of WireNumberTools
     * @return WireNumberTools
     */
    public function wireNumberools(): WireNumberTools
    {
        if ($this->wireNumberTools) {
            return $this->wireNumberTools;
        }

        return $this->wireNumberTools = new WireNumberTools();
    }

}
