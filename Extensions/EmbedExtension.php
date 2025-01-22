<?php

/**
 * Adds ability to embed markup to variables and render templates with data passed
 *
 */

declare(strict_types=1);

namespace PlatesForProcessWire\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use League\Plates\Template\Template;
use LogicException;
use PlatesForProcessWire\Extensions\Objects\Capture;

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

        $engine->registerFunction('embed', [$this, 'embed']);
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
            $this->embed($method);
        }

        return $this;
    }

    /**
     * Embeds
     */

    private ?string $embedTemplate = null;

    private array $templateData = [];

    /**
     * Start embed to render template with blocks assigned to variables and passed to template
     * as data
     *
     * embed will automatically insert content to the page when ended
     *
     * @param  string $name Name of template to render
     * @param  array  $data Data to pass to the rendered template
     * @return void
     * @throws LogicException
     */
    public function embed(string $name, array $data = []): void
    {
        $this->embedTemplate && throw new LogicException('You cannot nest embeds');

        $this->embedTemplate = $name;
        $this->templateData = $data;
    }

    /**
     * Alias for embed()
     */
    public function startEmbed(string $name, array $data = []): void
    {
        $this->embed($name, $data);
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
        $templateData = $this->templateData;

        $this->embedTemplate = null;
        $this->templateData = [];

        $template = new Template($this->engine, $embedTemplate);

        $template->insert($embedTemplate, $templateData);
    }

    /**
     * Alias for stopEmbed()
     *
     * @return string|void Depending on insert
     */
    public function endEmbed(): void
    {
        $this->stopEmbed();
    }

    /**
     * Embed Blocks
     */

    /**
     * A short single value embed that will be assigned to a variable without a block start/stop
     * when used within an embed or insertEmbed
     *
     * @param  string          $name  Name of embed that matches a variable in the template to render
     * @param  mixed $value Value to insert
     * @return void
     * @throws LogicException
     */
    public function blockValue(string $variableName, mixed $value): void
    {
        !$this->embedTemplate && throw new LogicException(
            'You must start an embed before inserting a block value'
        );

        $this->templateData[$variableName] = $value;

    }

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
            'You must provide the name of a template variable when starting a block'
        );

        !$this->embedTemplate && throw new LogicException(
            'You must start an embed before starting a block'
        );

        $this->activeBlock && throw new LogicException(
            'You cannot nest embed blocks'
        );

        $this->activeBlock = $variableName;

        $this->templateData[$this->activeBlock] = null;

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
        !$this->embedTemplate && throw new LogicException(
            'An embed must be started before block is ended'
        );

        !$this->activeBlock && throw new LogicException(
            'A block must be started before it can be stopped'
        );

        $this->templateData[$this->activeBlock] = ob_get_clean();

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

    /**
     * Starts a capture and returns a new Capture instance
     *
     * @param string|null $functions Optional function string executed on output via batch
     * @return Capture
     */
    public function capture(): Capture
    {
        return new Capture($this->template);
    }
}
