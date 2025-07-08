<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\AbstractHealthCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Tools\PhpStanTool;
use Vix\Syntra\Traits\HasBinaryTool;

class PhpStanCheckCommand extends AbstractHealthCommand
{
    use HasBinaryTool;
    protected string $sectionTitle = 'Running PHPStan...';
    protected string $successMessage = 'PHPStan analysis completed.';

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:phpstan')
            ->setDescription('Runs PHPStan static analysis.');
    }

    public function runCheck(): CommandResult
    {
        $this->findBinaryTool(new PhpStanTool());

        $args = [
            'analyse',
            '--error-format=json',
            '--no-progress',
            '--configuration=' . Config::getCommandOption(CommandGroup::HEALTH->value, self::class, 'config', 'phpstan.neon'),
            Project::getRootPath(),
        ];

        $result = Process::run($this->binary, $args);

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
}
