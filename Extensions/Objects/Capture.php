<?php

declare(strict_types=1);

namespace PlatesForProcessWire\Extensions\Objects;

use League\Plates\Template\Template;
use LogicException;
use Stringable;

class Capture implements Stringable
{
    private bool $captureStopped = false;

    private mixed $capturedValue = '';

    public function __construct(
        private Template $template,
    ) {
        ob_start();
    }

    /**
     * Stops the current capture
     * @param  string|null $functions Optional function string executed on output via batch()
     * @return self
     * @throws LogicException
     */
    public function stop(?string $functions = null): self
    {
        $this->captureStopped && throw new LogicException('This capture has already been stopped');

        $this->capturedValue = ob_get_clean();
        $this->captureStopped = true;

        if ($functions) {
            $this->capturedValue = $this->template->batch($this->capturedValue, $functions);
        }

        return $this;
    }

    /**
     * Alias for stop()
     * @param  string|null $functions Optional function string executed on output via batch()
     * @return self
     * @throws LogicException
     */
    public function end(?string $functions = null): self
    {
        $this->captureStopped && throw new LogicException('This capture has already been ended');

        return $this->stop($functions);
    }

    /**
     * Returns the captured output
     * @return mixed
     */
    public function value(): mixed
    {
        !$this->captureStopped && throw new LogicException('Capture must be stopped before output');

        return $this->capturedValue;
    }

    /**
     * Implementation of Stringable
     * @return string
     * @throws LogicException
     */
    public function __toString(): string
    {
        return (string) $this->value();
    }
}
