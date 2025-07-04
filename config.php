<?php

declare(strict_types=1);

use Vix\Syntra\Commands\Analyze\FindBadPracticesCommand;
use Vix\Syntra\Commands\Analyze\FindDebugCallsCommand;
use Vix\Syntra\Commands\Analyze\FindLongMethodsCommand;
use Vix\Syntra\Commands\Analyze\FindTodosCommand;
use Vix\Syntra\Commands\Health\ComposerChecker;
use Vix\Syntra\Commands\Health\PhpStanChecker;
use Vix\Syntra\Commands\Health\PhpUnitChecker;
use Vix\Syntra\Commands\Health\ProjectCheckCommand;
use Vix\Syntra\Commands\Health\SecurityChecker;
use Vix\Syntra\Commands\Health\ComposerCheckCommand;
use Vix\Syntra\Commands\Health\PhpStanCheckCommand;
use Vix\Syntra\Commands\Health\PhpUnitCheckCommand;
use Vix\Syntra\Commands\Refactor\DocblockRefactorer;
use Vix\Syntra\Commands\Refactor\ImportRefactorer;
use Vix\Syntra\Commands\Refactor\PhpCsFixerRefactorer;
use Vix\Syntra\Commands\Refactor\RectorRefactorer;
use Vix\Syntra\Commands\Refactor\VarCommentsRefactorer;
use Vix\Syntra\Commands\Extension\Yii\YiiUserFindoneToIdentityCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiCanHelpersCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiCheckTranslationsCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiConvertAccessChainCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiDeleteShortcutCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiFindIdCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiFindShortcutsCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiAllCommand;
use Vix\Syntra\Commands\Extension\Yii\YiiUpdateShortcutCommand;
use Vix\Syntra\Commands\General\GenerateCommandCommand;
use Vix\Syntra\Commands\General\GenerateDocsCommand;

/**
 * Syntra Configuration
 *
 * This file controls both console and web interface settings.
 *
 * Structure:
 * - [group] => [
 *     [CommandClass] => bool|array: Command configuration
 * ]
 *
 * For detailed command config:
 * [CommandClass] => [
 *     'enabled' => bool: Enable/disable for console
 *     'web_enabled' => bool: Enable/disable for web interface
 *     'config' => string: Path to command-specific config file
 *     // ... other command-specific options
 * ]
 *
 * Global web settings:
 * 'web' => [
 *     'enabled' => bool: Global web interface toggle
 * ]
 */

return [
    // Global web interface settings
    'web' => [
        'enabled' => true,
    ],

    // Command configurations
    'refactor' => [
        DocblockRefactorer::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        ImportRefactorer::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        PhpCsFixerRefactorer::class => [
            'enabled' => true,
            'web_enabled' => true,
            'config' => PACKAGE_ROOT . '/php_cs_fixer.php',
        ],
        VarCommentsRefactorer::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        RectorRefactorer::class => [
            'enabled' => true,
            'web_enabled' => true,
            'config' => PACKAGE_ROOT . '/rector.php',
            'commands_config' => PACKAGE_ROOT . '/rector_only_custom.php',
        ],
    ],
    'health' => [
        ComposerChecker::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        PhpStanChecker::class => [
            'enabled' => true,
            'web_enabled' => true,
            'config' => PACKAGE_ROOT . '/phpstan.neon',
            'level' => 5
        ],
        PhpUnitChecker::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        ComposerCheckCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        PhpStanCheckCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        PhpUnitCheckCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        SecurityChecker::class => [
            'enabled' => false,
            'web_enabled' => false,
        ],
        ProjectCheckCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
    ],
    'analyze' => [
        FindTodosCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        FindDebugCallsCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        FindLongMethodsCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        FindBadPracticesCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
    ],
    'general' => [
        GenerateCommandCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        GenerateDocsCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
    ],
    'yii' => [
        YiiAllCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        YiiFindShortcutsCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        YiiFindIdCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        YiiUpdateShortcutCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        YiiDeleteShortcutCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        YiiCanHelpersCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        YiiCheckTranslationsCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        YiiConvertAccessChainCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        YiiUserFindoneToIdentityCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
    ],
    'laravel' => [
        //
    ],
];
