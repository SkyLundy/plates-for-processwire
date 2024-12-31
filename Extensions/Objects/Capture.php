<?php

declare(strict_types=1);

namespace Plates\Extensions\Objects;

use LogicException;
use Stringable;

class Capture implements Stringable
{
    private bool $captureStopped = false;

    private string $capturedValue = '';

    public function __construct()
    {
        ob_start();
    }

    /**
     * Stops the current capture
     * @return self
     * @throws LogicException
     */
    public function stop(): self
    {
        $this->captureStopped && throw new LogicException('This capture has already been stopped');

        $this->capturedValue = ob_get_clean();
        $this->captureStopped = true;

        return $this;
    }

    /**
     * Alias for stop()
     * @return self
     * @throws LogicException
     */
    public function end(): self
    {
        $this->captureStopped && throw new LogicException('This capture has already been ended');

        return $this->stop();
    }

    public function value(): string
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
        return $this->value();
    }
}
