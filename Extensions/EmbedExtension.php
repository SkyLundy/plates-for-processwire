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

        $engine->registerFunction('startCapture', [$this, 'startCapture']);
        $engine->registerFunction('stopCapture', [$this, 'stopCapture']);
        $engine->registerFunction('endCapture', [$this, 'endCapture']);

        $engine->registerFunction('captureTo', [$this, 'captureTo']);
        $engine->registerFunction('captureValue', [$this, 'captureValue']);
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
    public function captureTo(string $variableName, string|int|float|null $value): void
    {
        !$this->embedTemplate && throw new LogicException(
            'You must start an embed before capturing a value'
        );

        $this->insertData[$variableName] = $value;

    }

    /**
     * Alias for captureTo()
     */
    public function captureValue(string $variableName = '', string|int|float|null $value): void
    {
        $this->captureTo($variableName, $value);
    }

    private ?string $activeCapture = null;

    /**
     * Starts a capture block
     * @param  string $name Name of the capture that matches a variable in the template to render
     * @return void
     * @throws LogicException
     */
    public function startCapture(string $variableName): void
    {
        !$variableName && throw new LogicException(
            'You must provide the name of a variable when capturing'
        );

        !$this->embedTemplate && throw new LogicException(
            'You must start an embed before capturing'
        );

        $this->activeCapture && throw new LogicException(
            'You cannot nest embed captures'
        );

        $this->activeCapture = $variableName;

        $this->insertData[$this->activeCapture] = null;

        ob_start();
    }

    /**
     * Stops capturing the current block and stores the embedd value for later rendering
     * @return void
     * @throws LogicException
     */
    public function stopCapture(): void
    {
        !$this->embedTemplate && throw new LogicException('An embed must be started before capturing');
        !$this->activeCapture && throw new LogicException('A capture must be started before it can be stopped');

        $this->insertData[$this->activeCapture] = ob_get_clean();

        $this->activeCapture = null;
    }

    /**
     * Alias for stopCapture()
     */
    public function endCapture(): void
    {
        $this->stopCapture();
    }
}
