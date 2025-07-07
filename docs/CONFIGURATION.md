# Syntra Configuration Guide

This document explains how to configure Syntra for both console and web interfaces using the unified `config.php` file.

## Overview

Syntra uses a single configuration file (`config.php`) that controls:

-   **Console Access**: Which commands are available via CLI
-   **Web Access**: Which commands are available via web interface
-   **Command Settings**: Tool-specific configurations (like config file paths)
-   **Security Settings**: Web interface security controls

Default group names are provided as enum cases in `Vix\\Syntra\\Enums\\CommandGroup` to
avoid typos when referencing configuration sections.

## Configuration Structure

```php
<?php

return [
    // Global web interface settings
    'web' => [
        'enabled' => true,           // Enable/disable entire web interface
        'require_auth' => false,     // Future: authentication requirement
        'allowed_ips' => [],         // IP whitelist (empty = allow all)
    ],

    // Command group configurations
    'group_name' => [
        CommandClass::class => [
            'enabled' => true,           // Console access (required)
            'web_enabled' => true,       // Web access (optional, defaults to true)
            'config' => '/path/to/config', // Command-specific config file
            // ... other command-specific options
        ],
    ],
];
```

## Configuration Options

### Global Web Settings

| Option         | Type    | Default | Description                      |
| -------------- | ------- | ------- | -------------------------------- |
| `enabled`      | `bool`  | `true`  | Global toggle for web interface  |
| `require_auth` | `bool`  | `false` | Future: require authentication   |
| `allowed_ips`  | `array` | `[]`    | IP whitelist (empty = allow all) |

### Command Configuration

Each command can be configured using either:

1. **Boolean**: `CommandClass::class => true`

    - Enables command for console
    - Enables command for web (default)

2. **Array**: `CommandClass::class => ['enabled' => true, 'web_enabled' => false]`
    - Fine-grained control over console and web access
    - Additional command-specific options

#### Common Command Options

| Option        | Type     | Default | Description                              |
| ------------- | -------- | ------- | ---------------------------------------- |
| `enabled`     | `bool`   | -       | **Required**: Enable command for console |
| `web_enabled` | `bool`   | `true`  | Enable command for web interface         |
| `config`      | `string` | -       | Path to command-specific config file     |

## Examples

### Basic Configuration

```php
use Vix\Syntra\Enums\CommandGroup;

return [
    'web' => ['enabled' => true],

    CommandGroup::ANALYZE->value => [
        FindTodosCommand::class => true, // Enabled for both console and web
    ],

    CommandGroup::HEALTH->value => [
        ProjectCheckCommand::class => [
            'enabled' => true,     // Console access
            'web_enabled' => true, // Web access
        ],
    ],
];
```

### Security-Focused Configuration

```php
return [
    // Disable web interface entirely
    'web' => ['enabled' => false],

    // Or enable with restrictions
    'web' => [
        'enabled' => true,
        'allowed_ips' => ['127.0.0.1', '192.168.1.0/24'],
    ],

    'refactor' => [
        RectorRefactorer::class => [
            'enabled' => true,
            'web_enabled' => true,
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
            'web_enabled' => true,
            'config' => __DIR__ . '/config/php_cs_fixer.php',
        ],
        RectorRefactorer::class => [
            'enabled' => true,
            'web_enabled' => false, // Console only
            'config' => __DIR__ . '/config/rector.php',
        ],
    ],

    'health' => [
        PhpStanChecker::class => [
            'enabled' => true,
            'web_enabled' => true,
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
        'web_enabled' => false,        // Disable web access
    ],
],
```

### Web Interface Security

1. **Disable in Production**:

    ```php
    'web' => ['enabled' => false],
    ```

2. **IP Restrictions**:

    ```php
    'web' => [
        'enabled' => true,
        'allowed_ips' => ['127.0.0.1', '10.0.0.0/8'],
    ],
    ```

3. **Command Restrictions**:
    ```php
    // Disable all refactor commands for web
    'refactor' => [
        DocblockRefactorer::class => [
            'enabled' => true,
            'web_enabled' => false,
        ],
    ],
    ```

## Migration from Separate Config Files

If you previously used `web-config.php`, merge it into `config.php`:

**Old `web-config.php`:**

```php
return [
    'enabled' => true,
    'health' => [
        'enabled' => true,
        ProjectCheckCommand::class => true,
    ],
];
```

**New unified `config.php`:**

```php
return [
    'web' => ['enabled' => true],
    'health' => [
        ProjectCheckCommand::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
    ],
];
```

## Validation

Test your configuration:

```bash
# Check console commands
vendor/bin/syntra list

# Check web commands (if web interface enabled)
curl http://localhost:8000/api/commands

# Test command execution
vendor/bin/syntra analyze:find-todos --dry-run
```

## Best Practices

1. **Start Conservative**: Begin with `web_enabled => false` for refactor commands
2. **Regular Review**: Periodically review and update your configuration
3. **Version Control**: Keep `config.php` in version control
4. **Environment Specific**: Consider different configs for dev/staging/production
5. **Reset File Cache**: Use `File::clearCache()` or run commands with `--no-cache` when working on temporary directories or between test runs.

## Troubleshooting

### Command Not Available

1. Check `enabled => true` in config
2. Verify command class exists and is properly imported
3. Check for typos in class names

### Web Command Not Showing

1. Verify `web.enabled => true`
2. Check command has `web_enabled => true` (or not explicitly set to false)
3. Check web server has read access to config file

### Configuration Not Loading

1. Ensure `config.php` exists in project root
2. Check PHP syntax with `php -l config.php`
3. Verify file permissions are readable by web server
