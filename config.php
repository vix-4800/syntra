<?php

declare(strict_types=1);

use Vix\Syntra\Commands\Analyze\AnalyzeAllCommand;
use Vix\Syntra\Commands\Analyze\FindBadPracticesCommand;
use Vix\Syntra\Commands\Analyze\FindDebugCallsCommand;
use Vix\Syntra\Commands\Analyze\FindLongMethodsCommand;
use Vix\Syntra\Commands\Analyze\FindTodosCommand;
use Vix\Syntra\Commands\Analyze\FindTyposCommand;
use Vix\Syntra\Commands\Analyze\StrictTypesCoverageCommand;
use Vix\Syntra\Commands\Extension\Laravel\LaravelAllCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiAllCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiCanHelpersCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiCheckTranslationsCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiConvertAccessChainCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiDeleteShortcutCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiFindAllIdCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiFindOneIdCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiFindShortcutsCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiUpdateShortcutCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiUserFindoneToIdentityCommand;
use Vix\Syntra\Commands\General\GenerateCommandCommand;
use Vix\Syntra\Commands\General\GenerateDocsCommand;
use Vix\Syntra\Commands\General\InitCommand;
use Vix\Syntra\Commands\Health\ComposerCheckCommand;
use Vix\Syntra\Commands\Health\EditorConfigCheckCommand;
use Vix\Syntra\Commands\Health\PhpStanCheckCommand;
use Vix\Syntra\Commands\Health\PhpUnitCheckCommand;
use Vix\Syntra\Commands\Health\PhpVersionCheckCommand;
use Vix\Syntra\Commands\Health\ProjectCheckCommand;
use Vix\Syntra\Commands\Health\SecurityCheckCommand;
use Vix\Syntra\Commands\Refactor\DocblockRefactorer;
use Vix\Syntra\Commands\Refactor\ImportRefactorer;
use Vix\Syntra\Commands\Refactor\PhpCsFixerRefactorer;
use Vix\Syntra\Commands\Refactor\RectorRefactorer;
use Vix\Syntra\Commands\Refactor\RefactorAllCommand;
use Vix\Syntra\Commands\Refactor\VarCommentsRefactorer;
use Vix\Syntra\Enums\CommandGroup;

/**
 * Syntra Configuration
 *
 * This file controls console command settings.
 *
 * Structure:
 * - [group] => [
 *     [CommandClass] => bool|array: Command configuration
 * ]
 *
 * For detailed command config:
 * [CommandClass] => [
 *     'enabled' => bool: Enable/disable for console
 *     'config' => string: Path to command-specific config file
 *     // ... other command-specific options
 * ]
 *
 */

return [
    // Command configurations
    CommandGroup::REFACTOR->value => [
        RefactorAllCommand::class => [
            'enabled' => true,
        ],
        DocblockRefactorer::class => [
            'enabled' => true,
        ],
        ImportRefactorer::class => [
            'enabled' => true,
        ],
        PhpCsFixerRefactorer::class => [
            'enabled' => true,
            'config' => config_path('php_cs_fixer.php'),
        ],
        VarCommentsRefactorer::class => [
            'enabled' => true,
        ],
        RectorRefactorer::class => [
            'enabled' => true,
            'config' => config_path('rector.php'),
            'commands_config' => config_path('rector_only_custom.php'),
        ],
    ],
    CommandGroup::HEALTH->value => [
        PhpVersionCheckCommand::class => [
            'enabled' => true,
        ],
        ComposerCheckCommand::class => [
            'enabled' => true,
        ],
        EditorConfigCheckCommand::class => [
            'enabled' => true,
        ],
        PhpStanCheckCommand::class => [
            'enabled' => true,
            'config' => config_path('phpstan.neon'),
        ],
        PhpUnitCheckCommand::class => [
            'enabled' => true,
        ],
        SecurityCheckCommand::class => [
            'enabled' => true,
        ],
        ProjectCheckCommand::class => [
            'enabled' => true,
        ],
    ],
    CommandGroup::ANALYZE->value => [
        AnalyzeAllCommand::class => [
            'enabled' => true,
        ],
        FindTodosCommand::class => [
            'enabled' => true,
            'todo_tags' => [
                'TODO',
                'FIXME',
                '@todo',
                '@fixme',
                '@deprecated',
                '@note',
                // '@see',
                '@hack',
                '@internal',
            ],
        ],
        FindDebugCallsCommand::class => [
            'enabled' => true,
            'debug_functions' => [
                'var_dump',
                'print_r',
                'dd',
                'dump',
                'ray',
                'die',
                'exit',
                'logger(',
                'eval',
                'xdebug_break',
            ],
        ],
        FindLongMethodsCommand::class => [
            'enabled' => true,
        ],
        FindBadPracticesCommand::class => [
            'enabled' => true,
        ],
        FindTyposCommand::class => [
            'enabled' => true,
        ],
        StrictTypesCoverageCommand::class => [
            'enabled' => true,
        ],
    ],
    CommandGroup::GENERAL->value => [
        GenerateCommandCommand::class => [
            'enabled' => true,
        ],
        GenerateDocsCommand::class => [
            'enabled' => true,
        ],
        InitCommand::class => [
            'enabled' => true,
        ],
    ],
    CommandGroup::YII->value => [
        YiiAllCommand::class => [
            'enabled' => true,
        ],
        YiiFindShortcutsCommand::class => [
            'enabled' => true,
        ],
        YiiFindOneIdCommand::class => [
            'enabled' => true,
        ],
        YiiFindAllIdCommand::class => [
            'enabled' => true,
        ],
        YiiUpdateShortcutCommand::class => [
            'enabled' => true,
        ],
        YiiDeleteShortcutCommand::class => [
            'enabled' => true,
        ],
        YiiCanHelpersCommand::class => [
            'enabled' => true,
        ],
        YiiCheckTranslationsCommand::class => [
            'enabled' => true,
        ],
        YiiConvertAccessChainCommand::class => [
            'enabled' => true,
        ],
        YiiUserFindoneToIdentityCommand::class => [
            'enabled' => true,
        ],
    ],
    CommandGroup::LARAVEL->value => [
        LaravelAllCommand::class => [
            'enabled' => true,
        ],
    ],
];
