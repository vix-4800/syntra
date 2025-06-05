<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Utils\ProcessRunner;

class ComposerChecker
{
    public function __construct(
        private readonly ProcessRunner $processRunner,
        private readonly string $projectRoot
    ) {
        //
    }

    public function run(): array
    {
        $result = $this->processRunner->run(
            'composer',
            ['outdated', '--direct', '--format=json'],
            ['working_dir' => $this->projectRoot]
        );

        if ($result['exitCode'] !== 0) {
            return [
                'status' => 'error',
                'messages' => ["Composer errored out:\n" . $result['stderr']],
            ];
        }

        $json = json_decode($result['output'], true);
        if (!isset($json['installed']) || count($json['installed']) === 0) {
            return [
                'status' => 'ok',
                'messages' => ['All packages are up to date.'],
            ];
        }

        $packages = array_map(fn($pkg): string => "{$pkg['name']} ({$pkg['version']} → {$pkg['latest']})", $json['installed']);
        return [
            'status' => 'warning',
            'messages' => $packages,
        ];
    }
}
