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
use League\Plates\Template\Template;
use League\Plates\Extension\ExtensionInterface;
use LogicException;

class EmbedExtension implements ExtensionInterface
{
    public $engine;

    public $template;

    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        $this->engine = $engine;

        $engine->registerFunction('embedStart', [$this, 'embedStart']);
        $engine->registerFunction('embedEnd', [$this, 'embedEnd']);

        $engine->registerFunction('blockStart', [$this, 'blockStart']);
        $engine->registerFunction('blockEnd', [$this, 'blockEnd']);
    }

    /**
     * Rendering
     */

    private $embedTemplate = null;
    private $embedName = null;
    private $embedData = [];
    private $embedBlockName = null;

    /**
     * Begins collecting markup for an embed
     * @param  string $name Name of the embed to render
     * @param  array  $data Data passed to the template
     * @return void
     */
    public function embedStart(string $name, array $data = []): void
    {
        if ($this->embedName) {
            throw new LogicException('You cannot nest embeds within other embeds.');
        }

        $this->embedName = $name;
        $this->embedData = $data;

        $this->embedTemplate = new Template($this->engine, $this->embedName);
    }

    /**
     * Blocks are embeddable groups of content that are rendered within embeds
     * Starts capturing block data
     * @param  string $name Name of this block
     * @return void
     */
    public function blockStart(?string $name = null): void
    {
        if (!$name) {
            throw new LogicException('Embed blocks must be named.');
        }

        $this->embedBlockName = $name;

        $this->embedTemplate->start($name);
    }

    /**
     * Stops capturing block data
     */
    public function blockEnd(): void
    {
        if (!$this->embedBlockName) {
            throw new LogicException('An embed block must be started by name before ending.');
        }

        $this->embedTemplate->stop();

        $this->embedBlockName = null;
    }

    /**
     * Ends collecting markup for an embed
     */
    public function embedEnd(): void
    {
        if (!$this->embedName) {
            throw new LogicException('An embed must be started by name before ending');
        }

        $embedData = $this->embedData;
        $this->embedName = null;
        $this->embedData = null;

        echo $this->embedTemplate->render($embedData);
    }

}
