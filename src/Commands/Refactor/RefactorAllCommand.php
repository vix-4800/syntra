<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vix\Syntra\Commands\Extension\Laravel\LaravelAllCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiAllCommand;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Traits\CommandRunnerTrait;
use Vix\Syntra\Utils\ProjectDetector;

class RefactorAllCommand extends SyntraRefactorCommand
{
    use CommandRunnerTrait;

    protected DangerLevel $dangerLevel = DangerLevel::HIGH;

    private bool $runFramework = false;

/**
 * Configure the command options.
 */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('refactor:all')
            ->setDescription('Runs all enabled refactor commands in sequence')
            ->setHelp('Usage: vendor/bin/syntra refactor:all [--framework]')
            ->addOption('framework', null, InputOption::VALUE_NONE, 'Also run framework-specific refactorings (Yii or Laravel)');
    }

/**
 * Initialize internal state.
 */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->runFramework = (bool) $input->getOption('framework');

        parent::initialize($input, $output);
    }

/**
 * Perform the command actions.
 */
    public function perform(): int
    {
        $enabled = Config::getEnabledCommands();
        $commands = array_filter(
            $enabled,
            static fn (string $class): bool => str_contains($class, '\\Commands\\Refactor\\') && $class !== self::class
        );

        $hasErrors = false;
        foreach ($commands as $class) {
            $exitCode = $this->runCommand($class);
            if ($exitCode !== self::SUCCESS) {
                $hasErrors = true;
            }
        }

        if ($this->runFramework) {
            $detector = new ProjectDetector();
            $type = $detector->detect((string) Config::getProjectRoot());

            if ($type === ProjectDetector::TYPE_YII) {
                $exitCode = $this->runCommand(YiiAllCommand::class, $this->getForwardOptions());
                if ($exitCode !== self::SUCCESS) {
                    $hasErrors = true;
                }
            } elseif ($type === ProjectDetector::TYPE_LARAVEL) {
                $exitCode = $this->runCommand(LaravelAllCommand::class, $this->getForwardOptions());
                if ($exitCode !== self::SUCCESS) {
                    $hasErrors = true;
                }
            } else {
                $this->output->warning('Framework not detected; skipping framework-specific refactor.');
            }
        }

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Forward common options to sub-commands.
     *
     * @return array<string,mixed>
     */
    private function getForwardOptions(): array
    {
        $opts = [];
        if ($this->dryRun) {
            $opts['--dry-run'] = true;
        }
        if ($this->force) {
            $opts['--force'] = true;
        }

        return $opts;
    }
}
