<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Utils\ProcessRunner;

class PhpUnitChecker
{
    public function __construct(
        private readonly ProcessRunner $processRunner,
        private readonly string $projectRoot
    ) {
        //
    }

    public function run(): CommandResult
    {
        $binary = find_composer_bin('phpunit', $this->projectRoot);

        if (!$binary) {
            throw new MissingBinaryException('phpunit', 'composer require --dev phpunit/phpunit');
        }

        $result = $this->processRunner->run(
            $binary,
            [],
            ['working_dir' => $this->projectRoot]
        );

        if ($result->exitCode === 0) {
            return CommandResult::ok(['PHPUnit tests passed.']);
        }

        $output = trim($result->output ?: $result->stderr);
        $messages = $output === '' ? ["PHPUnit failed with exit code $result->exitCode."] : preg_split('/\r?\n/', $output);

        return CommandResult::error($messages);
    }
}
