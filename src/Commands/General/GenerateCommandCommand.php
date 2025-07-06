<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Utils\StubHelper;

class GenerateCommandCommand extends SyntraCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('general:generate-command')
            ->setDescription('Generates a scaffold for a new Symfony Console command')
            ->setHelp('Usage: vendor/bin/syntra general:generate-command [--group=GROUP] [--cli-name=NAME] [--desc=TEXT]')
            ->addOption('group', null, InputOption::VALUE_OPTIONAL, 'The command group')
            ->addOption('cli-name', null, InputOption::VALUE_OPTIONAL, 'The CLI name of the command (e.g. refactor:custom)')
            ->addOption('desc', null, InputOption::VALUE_OPTIONAL, 'Description of the command');
    }

    public function perform(): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $group = $this->input->getOption('group')
            ?? $helper->ask($this->input, $this->output, new Question('Command group (refactor/health/...): '));
        if (!$group || !preg_match('/^\w+$/', (string) $group)) {
            $this->output->error('Invalid group');
            return Command::FAILURE;
        }
        $group = strtolower((string) $group);

        $cliName = $this->input->getOption('cli-name')
            ?? $helper->ask($this->input, $this->output, new Question('CLI name (e.g. my-cool): '));
        if (!$cliName || !preg_match('/^[a-z][a-z0-9\-:]+$/', (string) $cliName)) {
            $this->output->error('Invalid CLI name format');
            return Command::FAILURE;
        }

        $desc = $this->input->getOption('desc')
            ?? $helper->ask($this->input, $this->output, new Question('Description: '));

        $name = str_replace('-', '', ucwords((string) $cliName, '-')) . 'Command';
        $namespace = 'Vix\Syntra\Commands\\' . ucfirst($group);
        $fileDir = PACKAGE_ROOT . '/src/Commands/' . ucfirst($group);
        $filePath = "$fileDir/$name.php";

        if (file_exists($filePath)) {
            $this->output->error("File already exists: $filePath");
            return Command::FAILURE;
        }

        $content = (new StubHelper("command"))->render([
            'namespace' => $namespace,
            'class' => $name,
            'cli_name' => $cliName,
            'desc' => $desc,
            'group' => $group,
        ]);

        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0777, true);
        }

        file_put_contents($filePath, $content);

        $this->output->success("Command successfully created: $filePath");
        $this->output->note("Don't forget to register this command in SyntraConfig!");

        return Command::SUCCESS;
    }
}
