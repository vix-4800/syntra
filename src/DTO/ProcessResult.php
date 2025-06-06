<?php

declare(strict_types=1);

namespace Vix\Syntra\DTO;

class ProcessResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $output,
        public readonly string $stderr,
    ) {
        //
    }
}
