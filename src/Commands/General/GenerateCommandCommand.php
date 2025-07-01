<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Vix\Syntra\SyntraCommand;

class GenerateCommandCommand extends SyntraCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('general:generate-command')
            ->setDescription('Generates a scaffold for a new Symfony Console command')
            ->addOption('group', null, InputOption::VALUE_OPTIONAL, 'The command group')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The PHP class name of the command')
            ->addOption('cli-name', null, InputOption::VALUE_OPTIONAL, 'The CLI name of the command (e.g. refactor:custom)')
            ->addOption('desc', null, InputOption::VALUE_OPTIONAL, 'Description of the command');
    }

    public function perform(): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $group = $this->input->getOption('group')
            ?? $helper->ask($this->input, $this->output, new Question('Command group (refactor/health/...): '));
        if (!$group || !preg_match('/^[a-zA-Z0-9_]+$/', $group)) {
            $this->output->error('Invalid group');
            return Command::FAILURE;
        }
        $group = strtolower($group);

        $name = $this->input->getOption('name')
            ?? $helper->ask($this->input, $this->output, new Question('PHP class name (e.g. MyCoolCommand): '));
        if (!$name || !preg_match('/^[A-Z][A-Za-z0-9_]+Command$/', (string) $name)) {
            $this->output->error('Class name must start with an uppercase letter and end with "Command"');
            return Command::FAILURE;
        }

        $cliName = $this->input->getOption('cli-name')
            ?? $helper->ask($this->input, $this->output, new Question('CLI name (e.g. my-cool): '));
        if (!$cliName || !preg_match('/^[a-z][a-z0-9\-:]+$/', (string) $cliName)) {
            $this->output->error('Invalid CLI name format');
            return Command::FAILURE;
        }

        $desc = $this->input->getOption('desc')
            ?? $helper->ask($this->input, $this->output, new Question('Description: '));

        $namespace = 'Vix\Syntra\Commands\\' . ucfirst($group);
        $fileDir = PACKAGE_ROOT . '/src/Commands/' . ucfirst($group);
        $filePath = "$fileDir/$name.php";

        if (file_exists($filePath)) {
            $this->output->error("File already exists: $filePath");
            return Command::FAILURE;
        }

        $stubPath = PACKAGE_ROOT . '/stubs/command.stub';
        $content = $this->renderStub($stubPath, [
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

    private function renderStub(string $stubPath, array $replacements): string
    {
        $content = file_get_contents($stubPath);
        foreach ($replacements as $key => $value) {
            if (!$value) {
                continue;
            }

            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        return $content;
    }
}
