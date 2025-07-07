<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

use Symfony\Component\Console\Style\SymfonyStyle;

class SpinnerIndicator extends AbstractProgressIndicator
{
    private array $spinnerChars = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];

    private int $spinnerIndex = 0;

    private bool $isRunning = false;

    private float $lastUpdate = 0.0;

    public function __construct(
        protected SymfonyStyle $output,
        private readonly float $updateInterval = 0.1
    ) {
        parent::__construct($output);
    }

    public function start(): void
    {
        $this->isRunning = true;
        $this->lastUpdate = microtime(true);
        $this->render();
    }

    public function advance(int $step = 1): void
    {
        if (!$this->isRunning) {
            return;
        }

        $now = microtime(true);
        if ($now - $this->lastUpdate < $this->updateInterval) {
            return;
        }

        $this->lastUpdate = $now;

        $this->spinnerIndex = ($this->spinnerIndex + 1) % count($this->spinnerChars);
        $this->render();
    }

    public function finish(): void
    {
        if (!$this->isRunning) {
            return;
        }

        $this->isRunning = false;
        $this->output->write("\r\033[K");
        $this->output->writeln("<info>✓</info> {$this->message}");
    }

    private function render(): void
    {
        $spinner = $this->spinnerChars[$this->spinnerIndex];
        $this->output->write(sprintf("\r<comment>%s</comment> %s", $spinner, $this->message));
    }
}
