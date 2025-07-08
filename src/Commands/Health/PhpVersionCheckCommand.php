<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Traits\HandlesResultTrait;

class PhpVersionCheckCommand extends SyntraCommand implements HealthCheckCommandInterface
{
    use HandlesResultTrait;

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:php-version')
            ->setDescription('Validates composer PHP version constraint.');
    }

    public function runCheck(): CommandResult
    {
        $composer = Project::getRootPath() . '/composer.json';
        if (!is_file($composer)) {
            return CommandResult::error(['composer.json not found.']);
        }

        $data = json_decode((string) file_get_contents($composer), true);
        $version = $data['require']['php'] ?? null;
        if ($version === null) {
            return CommandResult::warning(['PHP version requirement missing in composer.json']);
        }

        if (preg_match('/(\d+\.\d+(?:\.\d+)?)/', (string) $version, $m)) {
            $min = $m[1];
            if (version_compare($min, '7.4', '<')) {
                return CommandResult::warning(["PHP requirement seems outdated: \"php\": \"$version\""]);
            }

            return CommandResult::ok(["PHP requirement: \"$version\""]);
        }

        return CommandResult::warning(["Unable to determine PHP version from \"$version\""]);
    }

    public function perform(): int
    {
        $this->output->section('Checking PHP version requirement...');

        $result = $this->runCheck();

        return $this->handleResult($result, 'PHP version check complete.');
    }
}
