<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Fixtures;

use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Utils\PackageInstaller;

class DummyInstaller extends PackageInstaller
{
    /** @var string[] */
    public array $commands = [];

    public function install(string $command): CommandResult
    {
        $this->commands[] = $command;
        return CommandResult::ok(['done']);
    }
}
