<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\CanHelpersRector;
use Vix\Syntra\Commands\Refactor\RectorRefactorer;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Exceptions\MissingBinaryException;

class YiiCanHelpersCommand extends SyntraRefactorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:can-helpers')
            ->setDescription('Replaces can()/!can() chains with canAny(), canAll(), cannotAny(), or cannotAll()')
            ->setHelp('');
    }

    public function perform(): int
    {
        $binary = find_composer_bin('rector', $this->configLoader->getProjectRoot());

        if (!$binary) {
            throw new MissingBinaryException("rector", "composer require --dev rector/rector");
        }

        $result = $this->processRunner->run($binary, [
            $this->configLoader->getProjectRoot(),
            "--config=" . $this->configLoader->getCommandOption('refactor', RectorRefactorer::class, 'commands_config'),
            "--only=" . str_replace("::class", "", CanHelpersRector::class),
        ]);

        if ($result->exitCode === 0) {
            $this->output->success('Rector refactoring completed.');
        } else {
            $this->output->error('Rector refactoring crashed.');
        }

        return $result->exitCode;
    }
}
