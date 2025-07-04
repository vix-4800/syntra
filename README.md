# Syntra

**Syntra** is a unified CLI tool for PHP projects, designed to streamline health checks, code refactoring, and framework integrations. It automates routine tasks, enforces coding standards, and provides a modular architecture for extensibility.

## 🚀 Features

-   **Health Checks**: Verify project stability with Composer, PHPStan, PHPUnit, and more.
-   **Code Refactoring**: Automatically fix code with custom refactorers, Rector, and PHP-CS-Fixer.
-   **Static Analysis**: Detect todos, long methods, and unsafe debug calls.
-   **Framework Support**: Built-in tooling for Yii (Laravel, Symfony planned).
-   **Extensibility**: Generate and register new commands using stubs.

## 📦 Installation

```bash
composer require vix/syntra
```

## 🚀 Quick Start

1. **Install Syntra** in your PHP project:

    ```bash
    composer require vix/syntra
    ```

2. **Run your first health check**:

    ```bash
    vendor/bin/syntra health:project
    ```

3. **Explore available commands**:

    ```bash
    vendor/bin/syntra list
    ```

4. **Try the web interface** (optional):
    ```bash
    php web/start-server.php
    # Open http://localhost:8000 in your browser
    ```

## 📋 Usage

Run Syntra commands using the CLI:

```bash
vendor/bin/syntra [command] [options]
```

### Basic Examples

```bash
# Run health checks on current project
vendor/bin/syntra health:project

# Find TODO comments in specific directory
vendor/bin/syntra analyze:find-todos --path=/path/to/project

# Fix code style with dry-run (preview changes)
vendor/bin/syntra refactor:cs-fixer --dry-run

# Generate a new command
vendor/bin/syntra general:generate-command --group=analyze --cli-name=analyze:custom

```

# Call another command programmatically
```php
use Vix\Syntra\Commands\Health\ComposerCheckCommand;
use Vix\Syntra\Traits\CommandRunnerTrait;

class MyCommand extends SyntraCommand
{
    use CommandRunnerTrait;

    public function perform(): int
    {
        return $this->runCommand(ComposerCheckCommand::class);
    }
}
```

### Common Options

All commands support these standard options:

-   `--path`: Specify the root path of the project (defaults to current directory)
-   `--dry-run` / `-d`: Preview changes without applying them
-   `--force` / `-f`: Force execution, ignore warnings (refactor commands only)
-   `--help` / `-h`: Display help for the command
-   `--quiet` / `-q`: Only show errors
-   `--verbose` / `-v/-vv/-vvv`: Increase verbosity level

## ⚙️ Commands

### 🔍 Analyze

| Command                      | Description                                                                       | Options                        |
| ---------------------------- | --------------------------------------------------------------------------------- | ------------------------------ |
| `analyze:find-todos`         | Scans project files and collects all TODO, FIXME, @todo, @deprecated comments     | `--path`, `--dry-run`          |
| `analyze:find-debug-calls`   | Checks that var_dump, dd, print_r, eval, and other calls prohibited in production | `--path`, `--dry-run`          |
| `analyze:find-long-methods`  | Finds all methods or functions that exceed a specified number of lines            | `--path`, `--dry-run`, `--max` |
| `analyze:find-bad-practices` | Detects bad practices in code like magic numbers, nested ternaries                | `--path`, `--dry-run`          |

### 🏥 Health

| Command          | Description                                               | Options               |
| ---------------- | --------------------------------------------------------- | --------------------- |
| `health:composer` | Check Composer dependencies for updates | `--path`, `--dry-run` |
| `health:phpstan`  | Run PHPStan static analysis | `--path`, `--dry-run` |
| `health:phpunit`  | Execute PHPUnit tests | `--path`, `--dry-run` |
| `health:project` | Run all health checks (composer, phpstan, phpunit) | `--path`, `--dry-run` |

### 🔧 Refactor

| Command                 | Description                                                       | Danger Level | Options                          |
| ----------------------- | ----------------------------------------------------------------- | ------------ | -------------------------------- |
| `refactor:cs-fixer`     | Fixes code style using php-cs-fixer for the selected files        | 🟢 LOW       | `--path`, `--dry-run`, `--force` |
| `refactor:imports`      | Fixes incorrect order of docblocks and import statements          | 🟢 LOW       | `--path`, `--dry-run`, `--force` |
| `refactor:var-comments` | Standardizes @var comments to `/** @var Type $var */`             | 🟢 LOW       | `--path`, `--dry-run`, `--force` |
| `refactor:docblocks`    | Adds a file-level PHPDoc block and class PHPDoc blocks if missing | 🟡 MEDIUM    | `--path`, `--dry-run`, `--force` |
| `refactor:rector`       | Runs Rector for automated refactoring                             | 🔴 HIGH      | `--path`, `--dry-run`, `--force` |

### 🧠 General

| Command                    | Description                                                                   | Options                                                  |
| -------------------------- | ----------------------------------------------------------------------------- | -------------------------------------------------------- |
| `general:generate-command` | Generates a scaffold for a new Symfony Console command                        | `--path`, `--dry-run`, `--group`, `--cli-name`, `--desc` |
| `general:generate-docs`    | Scans Yii controllers and generates a markdown file listing all action routes | `--path`, `--dry-run`                                    |

### 🧩 Yii Framework Extensions

| Command                        | Description                                                                                          | Danger Level | Options                          |
| ------------------------------ | ---------------------------------------------------------------------------------------------------- | ------------ | -------------------------------- |
| `yii:all`                      | Runs all Yii-specific Rector refactorings in sequence                                                | 🟢 LOW       | `--path`, `--dry-run`, `--force` |
| `yii:find-shortcuts`           | Converts `Model::find()->where([...])->one()/all()` into `Model::findOne([...])` or `findAll([...])` | 🟢 LOW       | `--path`, `--dry-run`, `--force` |
| `yii:find-id`                  | Replaces `Model::findOne(['id' => $id])` with `Model::findOne($id)`                                  | 🟢 LOW       | `--path`, `--dry-run`, `--force` |
| `yii:update-shortcut`          | Replaces `Model::find()->where([...])->update([...])` with `Model::updateAll([...], [...])`          | 🟢 LOW       | `--path`, `--dry-run`, `--force` |
| `yii:delete-shortcut`          | Replaces `Model::find()->where([...])->delete()` with `Model::deleteAll([...])`                      | 🟢 LOW       | `--path`, `--dry-run`, `--force` |
| `yii:can-helpers`              | Replaces `can()/!can()` chains with `canAny()`, `canAll()`, `cannotAny()`, or `cannotAll()`          | 🟢 LOW       | `--path`, `--dry-run`, `--force` |
| `yii:check-translations`       | Checks Yii::t translations: finds missing and unused keys across all categories                      | N/A          | `--path`, `--dry-run`, `--lang`  |
| `yii:convert-access-chain`     | Replaces `user->identity->hasAccessChain/hasNoAccessChain` with `user->canAny/cannotAny`             | N/A          | `--path`, `--dry-run`            |
| `yii:user-findone-to-identity` | Replaces redundant `User::findOne(...)` lookups for current user with `Yii::$app->user->identity`    | N/A          | `--path`, `--dry-run`            |

## 📁 Configuration

Configuration is defined in PHP via `config.php`, allowing you to enable/disable commands or set options per tool. Example:

```php
return [
    'refactor' => [
        PhpCsFixerRefactorer::class => [
            'enabled' => true,
            'config' => __DIR__ . '/php_cs_fixer.php',
        ],
    ],
    'yii' => [
        YiiFindShortcutsCommand::class => true,
    ],
];
```

## 🌐 Web Interface

Syntra now includes a modern web interface for running commands directly from your browser!

### Quick Start

Start the development server:

```bash
php web/start-server.php
```

Then open your browser to `http://localhost:8000`

### Features

-   **Beautiful UI**: Modern, responsive design
-   **Real-time Execution**: Execute commands and see results instantly
-   **Formatted Output**: Nicely formatted command output with syntax highlighting
-   **Security**: Configurable command access control via `web-config.php`
-   **Command Groups**: Commands organized by categories
-   **Options Support**: Full support for `--path` and `--dry-run` options

### Security Configuration

Control which commands are available via web in the main `config.php`:

```php
return [
    // Global web interface settings
    'web' => [
        'enabled' => true,
        'require_auth' => false,
        'allowed_ips' => [], // Empty = allow all IPs
    ],

    // Command configurations (both console and web)
    'health' => [
        ProjectCheckCommand::class => [
            'enabled' => true,        // Console access
            'web_enabled' => true,    // Web access
        ],
    ],
    'refactor' => [
        PhpCsFixerRefactorer::class => [
            'enabled' => true,
            'web_enabled' => true,
            'require_dry_run' => false, // Set to true to force dry-run
        ],
    ],
];
```

**⚠️ Security Note**: For production use, implement proper authentication and consider disabling commands that modify files.

See `web/README.md` for detailed setup and configuration instructions.

## 💡 Tips & Best Practices

### Safety First

-   **Always use `--dry-run`** first to preview changes before applying them
-   **Commit your code** to version control before running refactor commands
-   **Start with analysis commands** (`analyze:*`) to understand your codebase
-   **Use health checks** regularly in your CI/CD pipeline

### Workflow Recommendations

1. **Daily Health Checks**:

    ```bash
    # Run everything
    vendor/bin/syntra health:project

    # Or individually
    vendor/bin/syntra health:composer
    vendor/bin/syntra health:phpstan
    vendor/bin/syntra health:phpunit
    ```

2. **Code Analysis Before Refactoring**:

    ```bash
    vendor/bin/syntra analyze:find-todos
    vendor/bin/syntra analyze:find-debug-calls
    vendor/bin/syntra analyze:find-long-methods
    ```

3. **Safe Refactoring Order** (with `--dry-run` first):

    ```bash
    # Low risk first
    vendor/bin/syntra refactor:imports --dry-run
    vendor/bin/syntra refactor:var-comments --dry-run
    vendor/bin/syntra refactor:cs-fixer --dry-run

    # Higher risk last
    vendor/bin/syntra refactor:docblocks --dry-run
    vendor/bin/syntra refactor:rector --dry-run
    ```

4. **Framework-Specific Optimizations** (Yii example):
    ```bash
    vendor/bin/syntra yii:find-shortcuts --dry-run
    vendor/bin/syntra yii:find-id --dry-run
    vendor/bin/syntra yii:check-translations
    ```

### Integration with CI/CD

Add to your CI pipeline:

```yaml
# .github/workflows/syntra.yml
- name: Run Syntra Health Checks
  run: |
      vendor/bin/syntra health:project
      vendor/bin/syntra analyze:find-debug-calls
```

### Running Tests

Execute the PHPUnit test suite locally or in CI:

```bash
vendor/bin/phpunit
```

## 🤝 Contributing

Feel free to fork and contribute your own health checks, refactorers, or extensions via pull requests!

## 📄 License

Syntra is open-source software licensed under the [MIT License](LICENSE).
