<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProgressBarIndicator extends AbstractProgressIndicator
{
    private readonly ProgressBar $progressBar;

    public function __construct(SymfonyStyle $output, int $maxSteps)
    {
        parent::__construct($output);
        $this->progressBar = $output->createProgressBar($maxSteps);
        $this->progressBar->setFormat(
            "\033[0m%current%/%max% %bar% %percent:3s%%\nRemaining: %remaining:-10s% %memory:37s%"
        );
        $this->progressBar->setBarWidth(50);
        $this->progressBar->setBarCharacter("\033[32m●\033[0m");
        $this->progressBar->setEmptyBarCharacter("\033[31m●\033[0m");
        $this->progressBar->setProgressCharacter("\033[32m➤\033[0m");
    }

    public function start(): void
    {
        $this->progressBar->start();
    }

    public function advance(int $step = 1): void
    {
        $this->progressBar->advance($step);
    }

    public function finish(): void
    {
        $this->progressBar->finish();
        $this->output->newLine();
    }

    public function setMessage(string $message): void
    {
        $this->progressBar->setMessage($message);
    }
}
