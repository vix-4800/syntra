<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\HealthCheckCommandInterface;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Tools\PhpStanTool;
use Vix\Syntra\Traits\HandlesResultTrait;
use Vix\Syntra\Traits\FindsToolBinaryTrait;

class PhpStanCheckCommand extends SyntraCommand implements HealthCheckCommandInterface
{
    use HandlesResultTrait;
    use FindsToolBinaryTrait;

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:phpstan')
            ->setDescription('Runs PHPStan static analysis.');
    }

    public function runCheck(): CommandResult
    {
        $tool = new PhpStanTool();
        $binary = $this->findToolBinary($tool);

        $args = [
            'analyse',
            '--error-format=json',
            '--no-progress',
            '--configuration=' . Config::getCommandOption(CommandGroup::HEALTH->value, self::class, 'config', 'phpstan.neon'),
            Project::getRootPath(),
        ];

        $result = Process::run($binary, $args);

        if ($result->exitCode !== 0 && empty($result->output)) {
            return CommandResult::error(["PHPStan crashed:\n$result->stderr"]);
        }

        $json = @json_decode($result->output, true);
        if (!$json || !isset($json['totals'])) {
            return CommandResult::error(['PHPStan output is not parseable.']);
        }

        $totals = $json['totals'] + ['errors' => 0, 'file_errors' => 0];
        if ($totals['errors'] === 0 && $totals['file_errors'] === 0) {
            return CommandResult::ok(['No errors found by PHPStan.']);
        }

        $messages = [];
        foreach ($json['files'] ?? [] as $file => $data) {
            foreach ($data['messages'] ?? [] as $msg) {
                $line = isset($msg['line']) ? "({$msg['line']})" : '';
                $messages[] = "$file$line: {$msg['message']}";
            }
        }
        foreach ($json['errors'] ?? [] as $err) {
            $messages[] = "General: $err";
        }

        return CommandResult::warning($messages);
    }

    public function perform(): int
    {
        $this->output->section('Running PHPStan...');

        $result = $this->runCheck();

        return $this->handleResult($result, 'PHPStan analysis completed.', $this->failOnWarning);
    }
}
