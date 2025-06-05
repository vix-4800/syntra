<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

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

    public function run(): array
    {
        $args = [
            'analyse',
            "--level=$this->level",
            "--error-format=json",
            "--no-progress",
            "--configuration=$this->configPath",
            'src'
        ];

        $result = $this->processRunner->run(
            find_composer_bin('phpstan', $this->projectRoot),
            $args,
            ['working_dir' => $this->projectRoot]
        );

        if ($result['exitCode'] !== 0 && empty($result['output'])) {
            return [
                'status' => 'error',
                'messages' => ["PHPStan crashed:\n" . $result['stderr']],
            ];
        }

        $output = $result['output'];
        $json = @json_decode($output, true);
        if (!$json || !isset($json['totals'])) {
            return [
                'status' => 'error',
                'messages' => ['PHPStan output is not parseable.'],
            ];
        }

        if ($json['totals']['errors'] === 0) {
            return [
                'status' => 'ok',
                'messages' => ['No errors found by PHPStan.'],
            ];
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

        return [
            'status'   => 'warning',
            'messages' => $messages,
        ];
    }
}
