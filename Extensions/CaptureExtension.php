<?php

/**
 * Adds ability to capture markup to variables and render templates with data passed
 *
 */

declare(strict_types=1);

namespace Plates\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use League\Plates\Template\Template;
use LogicException;

class CaptureExtension implements ExtensionInterface
{
    public $engine;

    public $template;

    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        $this->engine = $engine;

        $engine->registerFunction('startCapture', [$this, 'startCapture']);
        $engine->registerFunction('endCapture', [$this, 'endCapture']);
        $engine->registerFunction('stopCapture', [$this, 'stopCapture']);

        $engine->registerFunction('startInsertCapture', [$this, 'startInsertCapture']);
        $engine->registerFunction('stopInsertCapture', [$this, 'stopInsertCapture']);

        $engine->registerFunction('captureTo', [$this, 'captureTo']);

        $engine->registerFunction('startCaptureBlock', [$this, 'startCaptureBlock']);
        $engine->registerFunction('stopCaptureBlock', [$this, 'stopCaptureBlock']);
    }

    private $captureStarted = false;

    /**
     * Begins capturing markup
     * A capture requires manually outputting the return value of endCapture()
     *
     * @return void
     */
    public function startCapture(): void
    {
        $this->captureStarted = true;

        ob_start();
    }

    /**
     * Ends capturing markup
     * Returns the captured markup for outputting to the page or assigning to a variable
     *
     * @return string Captured markup
     * @throws LogicException
     */
    public function endCapture(): string
    {
        !$this->captureStarted && throw new LogicException(
            'A capture must be started by before it can be ended'
        );

        $this->captureStarted = false;

        return ob_get_clean();
    }

    /**
     * Ends capturing markup
     *
     * @return string Captured markup
     * @throws LogicException
     */
    public function stopCapture(): string
    {
        !$this->captureStarted && throw new LogicException(
            'A capture must be started by before it can be stopped'
        );

        return $this->endCapture();
    }

    private ?string $insertTemplate = null;
    private array $insertData = [];
    private ?string $activeCapture = null;

    /**
     * Start capture to render template with blocks assigned to variables and passed to template
     * as data
     *
     * An insertCapture will automatically output (echo) the content and blocks captured
     *
     * @param  string $name Name of template to render
     * @param  array  $data Data to pass to the rendered template
     * @return void
     * @throws LogicException
     */
    public function startInsertCapture(string $name, array $data = []): void
    {
        $this->insertTemplate && throw new LogicException('You cannot nest capture inserts');

        $this->insertTemplate = $name;
        $this->insertData = $data;
    }

    /**
     * A short single value capture that will be assigned to a variable without a block start/stop
     * when used within a capture or insertCapture
     *
     * @param  string          $name  Name of capture that matches a variable in the template to render
     * @param  string|int|null $value Value to assign
     * @return void
     * @throws LogicException
     */
    public function captureTo(string $name, string|int|null $value): void
    {
        !$this->insertTemplate && throw new LogicException(
            'A capture to can only be called within an insert capture'
        );

        $this->insertData[$name] = $value;
    }

    /**
     * Starts a capture block
     * @param  string $name Name of the capture that matches a variable in the template to render
     * @return void
     * @throws LogicException
     */
    public function startCaptureBlock(string $name): void
    {
        $this->activeCapture && throw new LogicException('You cannot nest capture blocks');

        $this->activeCapture = $name;
        $this->insertData[$this->activeCapture] = null;

        ob_start();
    }

    /**
     * Stops capturing the current block and stores the captured value for later rendering
     * @return void
     * @throws LogicException
     */
    public function stopCaptureBlock(): void
    {
        !$this->activeCapture && throw new LogicException(
            'A capture block must be started before it can be ended'
        );

        $this->insertData[$this->activeCapture] = ob_get_clean();

        $this->activeCapture = null;
    }

    /**
     * Stops the insert capture and renders the selected template
     * @return void
     * @throws LogicException
     */
    public function stopInsertCapture(): void
    {
        !$this->insertTemplate && throw new LogicException(
            'An capture insert must be started before it can be stopped'
        );

        $insertTemplate = $this->insertTemplate;
        $insertData = $this->insertData;

        $this->insertTemplate = null;
        $this->insertData = [];

        $template = new Template($this->engine, $insertTemplate);

        $template->insert($insertTemplate, $insertData);
    }

    /**
     * Alias for stopInsertCapture
     */
    public function endInsertCapture(): void
    {
        $this->stopInsertCapture();
    }
}
