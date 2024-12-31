<?php

/**
 * Adds ability to embed markup to variables and render templates with data passed
 *
 */

declare(strict_types=1);

namespace Plates\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use League\Plates\Template\Template;
use LogicException;
use Plates\Extensions\Objects\Capture;

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

        $engine->registerFunction('embed', [$this, 'getObject']);

        $engine->registerFunction('startEmbed', [$this, 'startEmbed']);
        $engine->registerFunction('stopEmbed', [$this, 'stopEmbed']);
        $engine->registerFunction('endEmbed', [$this, 'endEmbed']);

        $engine->registerFunction('startBlock', [$this, 'startBlock']);
        $engine->registerFunction('stopBlock', [$this, 'stopBlock']);
        $engine->registerFunction('endBlock', [$this, 'endBlock']);

        $engine->registerFunction('blockValue', [$this, 'blockValue']);

        $engine->registerFunction('capture', [$this, 'capture']);
    }

    public function getObject(?string $method = null, ...$args): self
    {
        if ($method && method_exists($this, $method)) {
            $this->$method(...$args);
        }

        if ($method && str_contains($method, '::')) {
            $this->startEmbed($method);
        }

        return $this;
    }

    /**
     * Embeds
     */

    private ?string $embedTemplate = null;
    private array $insertData = [];
    private ?string $activeEmbed = null;

    /**
     * Start embed to render template with blocks assigned to variables and passed to template
     * as data
     *
     * An insertEmbed will automatically output (echo) the content and blocks embedd
     *
     * @param  string $name Name of template to render
     * @param  array  $data Data to pass to the rendered template
     * @return void
     * @throws LogicException
     */
    public function startEmbed(string $name, array $data = []): void
    {
        $this->embedTemplate && throw new LogicException('You cannot nest embeds');

        $this->embedTemplate = $name;
        $this->insertData = $data;
    }


    /**
     * Stops the embed and renders the selected template
     * @return void
     * @throws LogicException
     */
    public function stopEmbed(): void
    {
        !$this->embedTemplate && throw new LogicException(
            'An embed must be started before it can be stopped'
        );

        $embedTemplate = $this->embedTemplate;
        $insertData = $this->insertData;

        $this->embedTemplate = null;
        $this->insertData = [];

        $template = new Template($this->engine, $embedTemplate);

        $template->insert($embedTemplate, $insertData);
    }

    /**
     * Alias for stopEmbed()
     */
    public function endEmbed(): void
    {
        $this->stopEmbed();
    }

    /**
     * A short single value embed that will be assigned to a variable without a block start/stop
     * when used within an embed or insertEmbed
     *
     * @param  string          $name  Name of embed that matches a variable in the template to render
     * @param  string|int|float|null $value Value to insert
     * @return void
     * @throws LogicException
     */
    public function blockValue(string $variableName, string|int|float|null $value): void
    {
        !$this->embedTemplate && throw new LogicException(
            'You must start an embed before capturing a value'
        );

        $this->insertData[$variableName] = $value;

    }

    /**
     * Embed Blocks
     */

    private ?string $activeBlock = null;

    /**
     * Starts an embed block
     *
     * @param  string $name Name of the capture that matches a variable in the template to render
     * @return void
     * @throws LogicException
     */
    public function startBlock(string $variableName): void
    {
        !$variableName && throw new LogicException(
            'You must provide the name of a variable when starting a block'
        );

        !$this->embedTemplate && throw new LogicException(
            'You must start an embed before starting a block'
        );

        $this->activeBlock && throw new LogicException(
            'You cannot nest embed blocks'
        );

        $this->activeBlock = $variableName;

        $this->insertData[$this->activeBlock] = null;

        ob_start();
    }

    /**
     * Stops capturing the current block and stores the value for later rendering
     *
     * @return void
     * @throws LogicException
     */
    public function stopBlock(): void
    {
        !$this->embedTemplate && throw new LogicException('An embed must be started before block is ended');
        !$this->activeBlock && throw new LogicException('A block must be started before it can be stopped');

        $this->insertData[$this->activeBlock] = ob_get_clean();

        $this->activeBlock = null;
    }

    /**
     * Alias for stopBlock()
     */
    public function endBlock(): void
    {
        $this->stopBlock();
    }

    /**
     * Captures
     */

    private ?string $captureActive = null;

    /**
     * Starts a capture and returns a new Capture instance
     *
     * @return Capture
     */
    public function capture(): Capture
    {
        return new Capture();
    }
}
