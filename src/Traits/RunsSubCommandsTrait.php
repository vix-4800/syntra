<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Vix\Syntra\Facades\Config;

trait RunsSubCommandsTrait
{
    /**
     * Run enabled commands filtered by namespace and/or group.
     *
     * @param string              $namespace Part of the FQCN to filter by.
     * @param string|null         $group     Command group to filter, or null for all.
     * @param array<string,mixed> $input     Input forwarded to each command.
     *
     * @return bool True when at least one command returned non-success.
     */
    protected function runSubCommands(
        string $namespace,
        ?string $group = null,
        array $input = []
    ): bool {
        $commands = $group !== null
            ? Config::getEnabledCommandsByGroup($group)
            : Config::getEnabledCommands();

        $commands = array_filter(
            $commands,
            static fn (string $class): bool => (
                $namespace === '' || str_contains($class, $namespace)
            ) && $class !== static::class
        );

        $hasErrors = false;
        foreach ($commands as $class) {
            $exitCode = $this->runCommand($class, $input);
            if ($exitCode !== self::SUCCESS) {
                $hasErrors = true;
            }
        }

        return $hasErrors;
    }
}
