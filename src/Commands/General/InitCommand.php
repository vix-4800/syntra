<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Facades\Installer;

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

        $projectRoot = Config::getProjectRoot();
        $files = [
            'config.php',
            'config/php_cs_fixer.php',
            'config/phpstan.neon',
            'config/rector.php',
            'config/rector_only_custom.php',
        ];

        foreach ($files as $rel) {
            $src = PACKAGE_ROOT . '/' . $rel;
            $dest = $projectRoot . '/' . $rel;

            if (!file_exists($dest) && file_exists($src)) {
                if (!is_dir(dirname($dest))) {
                    mkdir(dirname($dest), 0777, true);
                }
                copy($src, $dest);
                $display = File::makeRelative($dest, $projectRoot);
                $this->output->writeln("Created $display");
            }
        }

        $this->output->success('Syntra initialization completed.');

        return Command::SUCCESS;
    }
}
