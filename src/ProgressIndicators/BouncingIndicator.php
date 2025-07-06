<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

use Symfony\Component\Console\Style\SymfonyStyle;

class BouncingIndicator extends AbstractProgressIndicator
{
    private int $position = 0;

    private int $direction = 1;

    private bool $isRunning = false;

    public function __construct(protected SymfonyStyle $output, private readonly int $width = 10)
    {
        parent::__construct($output);
    }

    public function start(): void
    {
        $this->isRunning = true;
        $this->render();
    }

    public function advance(): void
    {
        if (!$this->isRunning) {
            return;
        }

        $this->position += $this->direction;

        if ($this->position <= 0) {
            $this->position = 0;
            $this->direction = 1;
        } elseif ($this->position >= $this->width - 1) {
            $this->position = $this->width - 1;
            $this->direction = -1;
        }

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
        $line = str_repeat(' ', $this->position) . '<comment>●</comment>' . str_repeat(' ', $this->width - $this->position - 1);
        $this->output->write("\r{$line} {$this->message}");
    }
}
