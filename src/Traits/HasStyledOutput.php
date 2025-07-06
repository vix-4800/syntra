<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

trait HasStyledOutput
{
    protected SymfonyStyle $output;

    /**
     * @param array<int, string>             $headers
     * @param array<int, array<int, string>> $rows
     */
    protected function table(array $headers, array $rows): void
    {
        $table = new Table($this->output);

        $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }
}
