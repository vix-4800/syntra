<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\FindOneFindAllShortcutRector;
use Vix\Syntra\Commands\Refactor\RectorRefactorer;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Exceptions\MissingBinaryException;

class YiiFindShortcutsCommand extends SyntraRefactorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:find-shortcuts')
            ->setDescription('Converts Model::find()->where([...])->one()/all() into Model::findOne([...]) or findAll([...])')
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
            "--config=" . $this->configLoader->getCommandOption('refactor', RectorRefactorer::class, 'config'),
            "--only=" . str_replace("::class", "", FindOneFindAllShortcutRector::class),
        ]);

        if ($result->exitCode === 0) {
            $this->output->success('Rector refactoring completed.');
        } else {
            $this->output->error('Rector refactoring crashed.');
        }

        return $result->exitCode;
    }
}
