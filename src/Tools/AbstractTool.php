<?php

declare(strict_types=1);

namespace Vix\Syntra\Tools;

abstract class AbstractTool implements ToolInterface
{
    public function __construct(
        protected string $binaryName,
        protected string $packageName,
        protected string $description,
        protected bool $dev = true
    ) {
    }

    public function binaryName(): string
    {
        return $this->binaryName;
    }

    public function packageName(): string
    {
        return $this->packageName;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function isDev(): bool
    {
        return $this->dev;
    }

    public function installCommand(): string
    {
        $devFlag = $this->dev ? '--dev ' : '';
        return sprintf('composer require %s%s', $devFlag, $this->packageName);
    }
}
