<?php

declare(strict_types=1);

use Vix\Syntra\Commands\Analyze\FindDebugCallsCommand;
use Vix\Syntra\Commands\Analyze\FindLongMethodsCommand;
use Vix\Syntra\Commands\Analyze\FindTodosCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiUserFindoneToIdentityCommand;
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
use Vix\Syntra\Commands\Extension\Yii\YiiCanHelpersCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiCheckModelSchemaCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiCheckTranslationsCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiConvertAccessChainCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiDeleteShortcutCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiFindIdCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiFindShortcutsCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiUpdateShortcutCommand;

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
            'commands_config' => PACKAGE_ROOT . '/rector_only_custom.php',
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
        FindLongMethodsCommand::class => true,
    ],
    'general' => [
        GenerateCommandCommand::class => true,
    ],
    'yii' => [
        YiiFindShortcutsCommand::class => true,
        YiiFindIdCommand::class => true,
        YiiUpdateShortcutCommand::class => true,
        YiiDeleteShortcutCommand::class => true,
        YiiCanHelpersCommand::class => true,
        YiiCheckTranslationsCommand::class => true,
        YiiCheckModelSchemaCommand::class => false,
        YiiConvertAccessChainCommand::class => true,
        YiiUserFindoneToIdentityCommand::class => true,
    ],
    'laravel' => [
        //
    ],
];
