<?php

declare(strict_types=1);

namespace Vix\Syntra\Tools;

interface ToolInterface
{
    public function binaryName(): string;

    public function packageName(): string;

    public function description(): string;

    public function isDev(): bool;

    public function installCommand(): string;
}
