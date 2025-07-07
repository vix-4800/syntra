<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\HealthCheckCommandInterface;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Exceptions\CommandException;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Traits\HandlesResultTrait;

class PhpStanCheckCommand extends SyntraCommand implements HealthCheckCommandInterface
{
    use HandlesResultTrait;

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:phpstan')
            ->setDescription('Runs PHPStan static analysis.');
    }

    public function runCheck(): CommandResult
    {
        $binary = find_composer_bin('phpstan', Config::getProjectRoot());

        if (!$binary) {
            throw new MissingBinaryException('phpstan', 'composer require --dev phpstan/phpstan');
        }

        $args = [
            'analyse',
            '--level=' . (int) Config::getCommandOption(CommandGroup::HEALTH->value, self::class, 'level', 0),
            '--error-format=json',
            '--no-progress',
            '--configuration=' . Config::getCommandOption(CommandGroup::HEALTH->value, self::class, 'config', 'phpstan.neon'),
            Config::getProjectRoot(),
        ];

        $result = Process::run($binary, $args);

        if ($result->exitCode !== 0 && empty($result->output)) {
            return CommandResult::error(["PHPStan crashed:\n$result->stderr"]);
        }

        $json = @json_decode($result->output, true);
        if (!$json || !isset($json['totals'])) {
            return CommandResult::error(['PHPStan output is not parseable.']);
        }

        if ($json['totals']['errors'] === 0) {
            return CommandResult::ok(['No errors found by PHPStan.']);
        }

        $messages = [];
        foreach ($json['files'] ?? [] as $file => $data) {
            foreach ($data['messages'] ?? [] as $msg) {
                $messages[] = "$file: $msg";
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

        try {
            $result = $this->runCheck();
        } catch (MissingBinaryException|CommandException $e) {
            $this->output->error($e->getMessage());
            return self::FAILURE;
        }

        return $this->handleResult($result, 'PHPStan analysis completed.', $this->failOnWarning);
    }
}
