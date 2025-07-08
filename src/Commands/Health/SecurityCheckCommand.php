<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\AbstractHealthCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Facades\Project;

class SecurityCheckCommand extends AbstractHealthCommand
{
    protected string $sectionTitle = 'Running Composer security audit...';
    protected string $successMessage = 'Composer security audit completed.';

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:security')
            ->setDescription('Checks Composer dependencies for known security vulnerabilities.');
    }

    public function runCheck(): CommandResult
    {
        $result = Process::run(
            'composer',
            ['audit', '--format=json'],
            ['working_dir' => Project::getRootPath()]
        );

        if ($result->exitCode !== 0) {
            return CommandResult::error(["Composer audit failed:\n{$result->stderr}"]);
        }

        $json = json_decode($result->output, true);
        if (!is_array($json) || !isset($json['advisories'])) {
            return CommandResult::error(['Composer audit output is not parseable.']);
        }

        if (empty($json['advisories'])) {
            return CommandResult::ok(['No security vulnerabilities found.']);
        }

        $messages = [];
        foreach ($json['advisories'] as $package => $items) {
            foreach ($items as $adv) {
                $link = $adv['link'] ?? '';
                $messages[] = trim("$package: $link");
            }
        }

        return CommandResult::warning($messages);
    }
}
