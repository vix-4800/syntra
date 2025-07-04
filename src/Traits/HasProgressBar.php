<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

trait HasProgressBar
{
    protected ?ProgressBar $progressBar = null;

    protected function startProgress(int $maxSteps): void
    {
        if ($this->output instanceof SymfonyStyle && $maxSteps > 0) {
            $this->progressBar = $this->output->createProgressBar($maxSteps);
            $this->progressBar->setFormat("\033[0m%current%/%max% %bar% %percent:3s%%\nRemaining: %remaining:-10s% %memory:37s%");
            $this->progressBar->setBarWidth(50);
            $this->progressBar->setBarCharacter("\033[32m●\033[0m");
            $this->progressBar->setEmptyBarCharacter("\033[31m●\033[0m");
            $this->progressBar->setProgressCharacter("\033[32m➤\033[0m");
            $this->progressBar->start();
        }
    }

    protected function advance(int $step = 1): void
    {
        if ($this->progressBar) {
            $this->progressBar->advance($step);
        }
    }

    protected function finishProgress(): void
    {
        if ($this->progressBar) {
            $this->progressBar->finish();
            $this->output->newLine(2);
            $this->progressBar = null;
        }
    }
}
