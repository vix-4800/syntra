<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Vix\Syntra\Enums\DangerLevel;

/**
 * Trait that adds danger level support to CLI commands.
 * Commands with a higher danger level require user confirmation unless forced.
 */
trait HasDangerLevel
{
    /**
     * The command's danger level (default: LOW).
     */
    protected DangerLevel $dangerLevel = DangerLevel::LOW;

    protected bool $force = false;

    /**
     * Get the current danger level of the command.
     */
    public function getDangerLevel(): DangerLevel
    {
        return $this->dangerLevel;
    }

    /**
     * Set the danger level dynamically.
     */
    protected function setDangerLevel(DangerLevel $level): static
    {
        $this->dangerLevel = $level;
        return $this;
    }

    /**
     * Ask for confirmation if the command is dangerous and not forced.
     */
    protected function askDangerConfirmation(InputInterface $input, OutputInterface $output): bool
    {
        if ($this->getDangerLevel() !== DangerLevel::LOW && !$input->getOption('force')) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');

            $output->writeln(sprintf(
                '<comment>Warning: the danger level of this command is marked as %s and it may be unsafe.</comment>',
                $this->getDangerLevel()->value,
            ));

            $question = new ConfirmationQuestion(
                '<fg=yellow>Are you sure you want to execute this command? (y/N): </>',
                false,
                '/^(y|yes)$/i'
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<comment>Execution cancelled.</comment>');
                return false;
            }
        }

        return true;
    }

    /**
     * Displays help message with danger level.
     */
    public function getHelp(): string
    {
        $baseHelp = parent::getHelp();
        $dangerLevel = $this->getDangerLevel();

        return sprintf(
            "%s\nDanger level: %s %s\n",
            $baseHelp,
            DangerLevel::getEmojiForLevel($dangerLevel),
            $dangerLevel->value,
        );
    }

    protected function addForceOption(): static
    {
        return $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Force execution, ignore warnings');
    }
}
