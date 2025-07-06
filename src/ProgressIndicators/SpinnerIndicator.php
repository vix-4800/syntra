<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

class SpinnerIndicator extends AbstractProgressIndicator
{
    private array $spinnerChars = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];

    private int $spinnerIndex = 0;

    private bool $isRunning = false;

    public function start(): void
    {
        $this->isRunning = true;
        $this->render();
    }

    public function advance(int $step = 1): void
    {
        if (!$this->isRunning) {
            return;
        }

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
