<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Utils\ProcessRunner;

class PhpStanChecker
{
    public function __construct(
        private readonly ProcessRunner $processRunner,
        private readonly string $projectRoot,
        private readonly int $level = 5,
        private readonly string $configPath = 'phpstan.neon'
    ) {
        //
    }

    public function run(): CommandResult
    {
        $args = [
            'analyse',
            "--level=$this->level",
            "--error-format=json",
            "--no-progress",
            "--configuration=$this->configPath",
            'src'
        ];

        $binary = find_composer_bin('phpstan', $this->projectRoot);

        if (!$binary) {
            throw new MissingBinaryException("phpstan", "composer require --dev phpstan/phpstan");
        }

        $result = $this->processRunner->run(
            $binary,
            $args,
            ['working_dir' => $this->projectRoot]
        );

        if ($result->exitCode !== 0 && empty($result->output)) {
            return CommandResult::error(["PHPStan crashed:\n$result->stderr"]);
        }

        $output = $result->output;
        $json = @json_decode($output, true);
        if (!$json || !isset($json['totals'])) {
            return CommandResult::error(["PHPStan output is not parseable."]);
        }

        if ($json['totals']['errors'] === 0) {
            return CommandResult::ok(["No errors found by PHPStan."]);
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
}
