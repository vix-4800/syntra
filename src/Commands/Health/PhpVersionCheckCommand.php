<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\AbstractHealthCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Facades\Project;

class PhpVersionCheckCommand extends AbstractHealthCommand
{
    protected string $sectionTitle = 'Checking PHP version requirement...';
    protected string $successMessage = 'PHP version check complete.';

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
}
