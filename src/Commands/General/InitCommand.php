<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\Tool;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Facades\Installer;
use Vix\Syntra\Facades\Project;

class InitCommand extends SyntraCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('general:init')
            ->setDescription('Initializes Syntra by installing optional packages and copying configuration files.')
            ->setHelp('Usage: vendor/bin/syntra general:init');
    }

    public function perform(): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $toolObjects = [
            Tool::RECTOR,
            Tool::PHP_CS_FIXER,
            Tool::PHP_STAN,
            Tool::PHPUNIT,
        ];

        foreach ($toolObjects as $tool) {
            $pkg = $tool->packageName();
            $question = new ConfirmationQuestion(
                "Install $pkg - {$tool->description()}? (y/N): ",
                false,
                '/^(y|yes)/i'
            );

            if ($helper->ask($this->input, $this->output, $question)) {
                $result = Installer::install($tool->installCommand());
                $this->handleResult($result, "$pkg installation finished.");
            }
        }

        $projectRoot = Project::getRootPath();
        $files = [
            'syntra.php',
            'config/php_cs_fixer.php',
            'config/phpstan.neon',
            'config/rector.php',
            'config/rector_only_custom.php',
        ];

        foreach ($files as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $relative = File::makeRelative($path, PACKAGE_ROOT);
            $dest = rtrim($projectRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relative;

            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0777, true);
            }

            copy($path, $dest);
            $display = File::makeRelative($dest, $projectRoot);

            $this->output->writeln("Created $display");
        }

        $this->output->success('Syntra initialization completed.');

        return Command::SUCCESS;
    }
}
