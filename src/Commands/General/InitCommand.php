<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Vix\Syntra\Commands\SyntraCommand;
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

        $packages = [
            'rector/rector' => 'Rector (code refactoring)',
            'friendsofphp/php-cs-fixer' => 'PHP CS Fixer (code style fixes)',
            'phpstan/phpstan' => 'PHPStan (static analysis)',
            'phpunit/phpunit' => 'PHPUnit (running tests)',
        ];

        foreach ($packages as $pkg => $desc) {
            $question = new ConfirmationQuestion(
                "Install $pkg - $desc? (y/N): ",
                false,
                '/^(y|yes)/i'
            );

            if ($helper->ask($this->input, $this->output, $question)) {
                $result = Installer::install("composer require --dev $pkg");
                $this->handleResult($result, "$pkg installation finished.");
            }
        }

        $projectRoot = Project::getRootPath();
        $files = [
            PACKAGE_ROOT . '/config.php',
            config_path('php_cs_fixer.php'),
            config_path('phpstan.neon'),
            config_path('rector.php'),
            config_path('rector_only_custom.php'),
        ];

        foreach ($files as $path) {
            if (file_exists($path)) {
                $dest = File::makeRelative($path, $projectRoot);

                copy($path, $dest);
                $display = File::makeRelative($dest, $projectRoot);

                $this->output->writeln("Created $display");
            }
        }

        $this->output->success('Syntra initialization completed.');

        return Command::SUCCESS;
    }
}
