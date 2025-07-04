# Syntra Web Interface

A modern, responsive web interface for running Syntra commands directly from your browser.

## Features

-   **Beautiful UI**: Modern, responsive design with dark/light themes
-   **Real-time Execution**: Execute commands and see results in real-time
-   **Formatted Output**: Nicely formatted command output with syntax highlighting
-   **Security**: Configurable command access control
-   **Command Groups**: Commands organized by categories (Health, Analyze, Refactor, etc.)
-   **Options Support**: Full support for command options like `--path` and `--dry-run`

## Quick Start

### Development Server

For development and testing, use the built-in PHP server:

```bash
# Start server on localhost:8000
php web/start-server.php

# Or specify custom host and port
php web/start-server.php 0.0.0.0 8080
```

Then open your browser to `http://localhost:8000`

### Production Setup

For production, configure your web server to serve the `web/` directory as the document root.

## Configuration

### Web Access Control

Control which commands are available via the web interface by editing the main `config.php` file:

```php
return [
    // Global web interface settings
    'web' => [
        'enabled' => true,
        'require_auth' => false,
        'allowed_ips' => [], // Empty = allow all IPs
    ],

    // Command configurations
    'health' => [
        ProjectCheckCommand::class => [
            'enabled' => true,        // Console access
            'web_enabled' => true,    // Web access
        ],
        // ... other commands
    ],

    'refactor' => [
        PhpCsFixerRefactorer::class => [
            'enabled' => true,
            'web_enabled' => true,
        ],
        // ... other commands
    ],
];
```

### Security Considerations

**⚠️ Important Security Notes:**

1. **Refactor Commands**: Commands that modify files should be used with caution. Consider:

    - Disabling refactor commands entirely in production
    - Using proper access control (authentication/authorization)

2. **File Permissions**: Ensure the web server has appropriate permissions to read your project files

3. **Network Access**: Consider restricting access to trusted networks only

4. **Authentication**: This web interface does not include authentication. For production use, implement proper authentication/authorization.

## Command Groups

Commands are organized into the following groups:

### 🔍 Health

-   **Project Check**: Run comprehensive health checks
-   **Composer**: Validate composer.json and dependencies
-   **PHPStan**: Static analysis checks
-   **PHPUnit**: Unit test validation

### 🔍 Analyze

-   **Find TODOs**: Scan for TODO, FIXME, and deprecated markers
-   **Find Debug Calls**: Locate debug statements like `var_dump()`, `dd()`
-   **Find Long Methods**: Identify methods that need refactoring
-   **Find Bad Practices**: Detect code smell and anti-patterns

### 🔧 Refactor

-   **PHP-CS-Fixer**: Apply coding standard fixes
-   **Rector**: Automated code transformations
-   **Docblocks**: Add missing documentation blocks
-   **Import Sorting**: Organize use statements
-   **Variable Comments**: Standardize `@var` comments

### 🧠 General

-   **Generate Command**: Create new Syntra commands
-   **Generate Docs**: Update project documentation

### 🧩 Framework Extensions

-   **Yii Commands**: Framework-specific optimizations
-   **Laravel Commands**: (Coming soon)

## API Endpoints

The web interface provides these API endpoints:

-   `GET /api/commands` - List available commands
-   `POST /api/execute` - Execute a command

### Execute Command API

```bash
curl -X POST http://localhost:8000/api/execute \
  -H "Content-Type: application/json" \
  -d '{
    "command": "Vix\\Syntra\\Commands\\Analyze\\FindTodosCommand",
    "options": {
      "path": "/path/to/project",
      "dry-run": true
    }
  }'
```

## Troubleshooting

### Commands Not Loading

1. Check that Composer dependencies are installed: `composer install`
2. Verify that the web server has read access to the project files
3. Check PHP error logs for any exceptions

### Command Execution Failures

1. Ensure the specified project path exists and is readable
2. Verify that required tools (PHPStan, PHP-CS-Fixer, etc.) are installed
3. Check that the web server process has appropriate permissions

### Performance Issues

1. Commands with large codebases may take time to execute
2. Consider using the `--dry-run` option for testing
3. Monitor server resources during command execution

## Development

### File Structure

```
web/
├── index.php              # Main entry point
├── start-server.php       # Development server script
└── README.md             # This file

src/Web/
├── WebApplication.php     # Main web application class
├── CommandExecutor.php    # Command execution handler
├── assets/
│   ├── style.css         # Styles
│   └── script.js         # JavaScript functionality
└── templates/
    └── index.html        # Main HTML template

config.php                # Unified configuration (console + web)
```

### Extending the Interface

To add new features:

1. **New API Endpoints**: Add routes in `WebApplication::run()`
2. **UI Components**: Modify templates and assets
3. **Command Options**: Update the options form in the template
4. **Output Formatting**: Enhance `CommandExecutor::parseOutput()`

## License

This web interface is part of Syntra and is licensed under the MIT License.
