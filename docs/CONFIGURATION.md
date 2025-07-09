# Syntra Configuration Guide

This document explains how to configure Syntra console commands using the unified `syntra.php` file.

## Overview

Syntra uses a single configuration file (`syntra.php`) that controls:

-   **Console Access**: Which commands are available via CLI
-   **Command Settings**: Tool-specific configurations (like config file paths)

Default group names are provided as enum cases in `Vix\\Syntra\\Enums\\CommandGroup` to
avoid typos when referencing configuration sections.

## Configuration Structure

```php
<?php

return [
    // Command group configurations
    'group_name' => [
        CommandClass::class => [
            'enabled' => true,           // Console access (required)
            'config' => '/path/to/config', // Command-specific config file
            // ... other command-specific options
        ],
    ],
];
```

## Configuration Options

### Command Configuration

Each command can be configured using either:

1. **Boolean**: `CommandClass::class => true`

    - Enables command for console

2. **Array**: `CommandClass::class => ['enabled' => true]`
    - Additional command-specific options

#### Common Command Options

| Option        | Type     | Default | Description                              |
| ------------- | -------- | ------- | ---------------------------------------- |
| `enabled`     | `bool`   | -       | **Required**: Enable command for console |
| `config`      | `string` | -       | Path to command-specific config file     |

## Examples

### Basic Configuration

```php
use Vix\Syntra\Enums\CommandGroup;

return [
    CommandGroup::ANALYZE->value => [
        FindTodosCommand::class => true, // Enabled for console
    ],

    CommandGroup::HEALTH->value => [
        ProjectCheckCommand::class => [
            'enabled' => true,
        ],
    ],
];
```

### Tool-Specific Configuration

```php
return [
    'refactor' => [
        PhpCsFixerRefactorer::class => [
            'enabled' => true,
            'config' => __DIR__ . '/config/php_cs_fixer.php',
        ],
        RectorRefactorer::class => [
            'enabled' => true,
            'config' => __DIR__ . '/config/rector.php',
        ],
    ],

    'health' => [
        PhpStanChecker::class => [
            'enabled' => true,
            'config' => __DIR__ . '/config/phpstan.neon',
            'level' => 5,
        ],
    ],
];
```

## Command Groups

### Core Commands

-   **`analyze`** (`CommandGroup::ANALYZE`): Code analysis commands (safe, read-only)
-   **`health`** (`CommandGroup::HEALTH`): Project health checks (safe, read-only)
-   **`refactor`** (`CommandGroup::REFACTOR`): Code modification commands (⚠️ **use with caution**)
-   **`general`** (`CommandGroup::GENERAL`): Utility commands

### Extension Commands

-   **`yii`** (`CommandGroup::YII`): Yii framework-specific commands
-   **`laravel`** (`CommandGroup::LARAVEL`): Laravel framework-specific commands

## Security Considerations

### Refactor Commands

Refactor commands modify your code files. For production use:

```php
'refactor' => [
    RectorRefactorer::class => [
        'enabled' => true,
    ],
],
```


## Migration from Separate Config Files


## Validation

Test your configuration:

```bash
# Check console commands
vendor/bin/syntra list

# Test command execution
vendor/bin/syntra analyze:find-todos --dry-run
```

## Best Practices

1. **Regular Review**: Periodically review and update your configuration
2. **Version Control**: Keep `syntra.php` in version control
3. **Environment Specific**: Consider different configs for dev/staging/production
4. **Reset File Cache**: Use `File::clearCache()` or run commands with `--no-cache` when working on temporary directories or between test runs.

## Troubleshooting

### Command Not Available

1. Check `enabled => true` in config
2. Verify command class exists and is properly imported
3. Check for typos in class names

### Configuration Not Loading

1. Ensure `syntra.php` exists in project root
2. Check PHP syntax with `php -l syntra.php`
