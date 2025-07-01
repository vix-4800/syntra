<?php

declare(strict_types=1);

namespace Vix\Syntra;

use Vix\Syntra\Commands\Analyze\FindDebugCallsCommand;
use Vix\Syntra\Commands\Analyze\FindTodosCommand;
use Vix\Syntra\Commands\General\GenerateCommandCommand;
use Vix\Syntra\Commands\Health\ComposerChecker;
use Vix\Syntra\Commands\Health\PhpStanChecker;
use Vix\Syntra\Commands\Health\PhpUnitChecker;
use Vix\Syntra\Commands\Health\ProjectCheckCommand;
use Vix\Syntra\Commands\Health\SecurityChecker;
use Vix\Syntra\Commands\Refactor\DocblockRefactorer;
use Vix\Syntra\Commands\Refactor\ImportRefactorer;
use Vix\Syntra\Commands\Refactor\PhpCsFixerRefactorer;
use Vix\Syntra\Commands\Refactor\RectorRefactorer;
use Vix\Syntra\Commands\Refactor\VarCommentsRefactorer;

class SyntraConfig
{
    public function commands(): array
    {
        return [
            'refactor' => [
                DocblockRefactorer::class => true,
                ImportRefactorer::class => true,
                PhpCsFixerRefactorer::class => [
                    'enabled' => true,
                    'config' => PACKAGE_ROOT . '/php_cs_fixer.php',
                ],
                VarCommentsRefactorer::class => true,
                RectorRefactorer::class => [
                    'enabled' => true,
                    'config' => PACKAGE_ROOT . '/rector.php',
                ],
            ],
            'health' => [
                ComposerChecker::class => true,
                PhpStanChecker::class => [
                    'enabled' => true,
                    'config' => PACKAGE_ROOT . '/phpstan.neon',
                    'level' => 5
                ],
                PhpUnitChecker::class => false,
                SecurityChecker::class => false,
                ProjectCheckCommand::class => true,
            ],
            'analyze' => [
                FindTodosCommand::class => true,
                FindDebugCallsCommand::class => true,
            ],
            'general' => [
                GenerateCommandCommand::class => true,
            ]
        ];
    }
}
