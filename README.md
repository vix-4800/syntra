# Syntra

**Syntra** is a unified CLI tool for PHP projects, designed to streamline health checks, code refactoring, and framework integrations. It automates routine tasks, enforces coding standards, and provides a modular architecture for extensibility.

## 🚀 Features

-   **Health Checks**: Verify project stability with Composer, PHPStan, PHPUnit, and security audits.
-   **Code Refactoring**: Automatically fix code with custom refactorers, Rector, and PHP-CS-Fixer.
-   **Static Analysis**: Detect todos, long methods, and unsafe debug calls.
-   **Typo Detection**: Spot misspellings in files and identifiers using Aspell (via Peck). Requires the `aspell` binary.
-   **Framework Support**: Built-in tooling for Yii (Laravel, Symfony planned).
-   **Extensibility**: Generate and register new commands using stubs.
-   **In-Memory Caching**: Reuses previously scanned file lists for faster repeated command execution.
-   **Cache Reset**: Call `File::clearCache()` to manually reset cached file lists when working with temporary directories or tests.
-   **No-Cache Option**: Use `--no-cache` to disable caching for a single command run.
-   **Facades**: Convenient static access to common services.
-   **Progress Indicators**: Choose between spinner, progress bar, bouncing, or none via the `ProgressIndicatorType` enum.
-   **Output Logging**: Use `--output-file=FILE` to save command output.

## 📦 Installation

```bash
composer require vix/syntra
```

## 🚀 Quick Start

1. **Install Syntra** in your PHP project:

    ```bash
    composer require vix/syntra
    ```

2. **Initialize Syntra**:

    ```bash
    vendor/bin/syntra general:init
    ```

3. **Run your first health check**:

    ```bash
    vendor/bin/syntra health:all
    ```

4. **Explore available commands**:

    ```bash
    vendor/bin/syntra list
    ```

## 📋 Usage

Run Syntra commands using the CLI:

```bash
vendor/bin/syntra [command] [options]
```

### Basic Examples

```bash
# Run health checks on current project
vendor/bin/syntra health:all

# Find TODO comments in specific directory
vendor/bin/syntra analyze:find-todos /path/to/project

# Detect typos in your project
vendor/bin/syntra analyze:find-typos /path/to/project
# Requires `aspell` to be installed and accessible in your PATH

# Fix code style with dry-run (preview changes)
vendor/bin/syntra refactor:cs-fixer --dry-run

# Generate a new command
vendor/bin/syntra general:generate-command --group=analyze --cli-name=analyze:custom
# Generate route docs and count usage (markdown includes a Refs column)
vendor/bin/syntra general:generate-docs --count-refs
```

### Common Options

All commands support these standard options (with an optional `[path]` argument to specify the project root):

-   `--dry-run` / `-d`: Preview changes without applying them
-   `--force` / `-f`: Force execution, ignore warnings (refactor commands only)
-   `--no-progress`: Disable progress output
-   `--no-cache`: Disable file caching (useful for temporary directories)
-   `--fail-on-warning`: Return exit code 1 if warnings were found
-   `--ci`: CI mode, implies `--no-progress` and `--fail-on-warning`
-   `--output-file=FILE`: Write all command output to FILE
-   `--help` / `-h`: Display help for the command
-   `--quiet` / `-q`: Only show errors
-   `--verbose` / `-v/-vv/-vvv`: Increase verbosity level

## ⚙️ Commands

### 🔍 Analyze

| Command                      | Description                                                                       | Options                                      |
| ---------------------------- | --------------------------------------------------------------------------------- | -------------------------------------------- |
| `analyze:find-todos`         | Scans project files and collects all TODO, FIXME, @todo, @deprecated comments     | `[path]`, `--dry-run`, `--no-cache`          |
| `analyze:find-debug-calls`   | Checks that var_dump, dd, print_r, eval, and other calls prohibited in production | `[path]`, `--dry-run`, `--no-cache`          |
| `analyze:find-long-methods`  | Finds all methods or functions that exceed a specified number of lines            | `[path]`, `--dry-run`, `--max`, `--no-cache` |
| `analyze:find-bad-practices` | Detects bad practices in code like magic numbers, nested ternaries                | `[path]`, `--dry-run`, `--no-cache`          |
| `analyze:find-typos`         | Detects misspellings in filenames and PHP identifiers                             | `[path]`, `--dry-run`, `--no-cache`          |
| `analyze:strict-types`       | Calculates percentage of files declaring `strict_types=1`                         | `[path]`, `--dry-run`, `--no-cache`          |
| `analyze:all`                | Runs all enabled analyze commands sequentially                                    | `[path]`, `--dry-run`, `--no-cache`          |

### 🏥 Health

| Command              | Description                                                  | Options                             |
| -------------------- | ------------------------------------------------------------ | ----------------------------------- |
| `health:composer`    | Check Composer dependencies for updates                      | `[path]`, `--dry-run`, `--no-cache` |
| `health:php-version` | Validate PHP version requirement in composer.json            | `[path]`, `--dry-run`, `--no-cache` |
| `health:phpstan`     | Run PHPStan static analysis                                  | `[path]`, `--dry-run`, `--no-cache` |
| `health:phpunit`     | Execute PHPUnit tests                                        | `[path]`, `--dry-run`, `--no-cache` |
| `health:security`    | Check Composer dependencies for known vulnerabilities        | `[path]`, `--dry-run`, `--no-cache` |
| `health:all`         | Run all health checks (composer, phpstan, phpunit, security) | `[path]`, `--dry-run`, `--no-cache` |

#### Additional checks to consider

-   Ensure an `.editorconfig` file is present for consistent formatting
-   Run PHP-CS-Fixer in dry-run mode to enforce PSR-12 style
-   Check strict types declarations using `analyze:strict-types`
-   Verify Composer package licenses match your project policy

### 🔧 Refactor

| Command                 | Description                                                       | Danger Level | Options                                                                            |
| ----------------------- | ----------------------------------------------------------------- | ------------ | ---------------------------------------------------------------------------------- |
| `refactor:cs-fixer`     | Fixes code style using php-cs-fixer for the selected files        | 🟢 LOW       | `[path]`, `--dry-run`, `--no-cache`, `--force`                                     |
| `refactor:imports`      | Fixes incorrect order of docblocks and import statements          | 🟢 LOW       | `[path]`, `--dry-run`, `--no-cache`, `--force`                                     |
| `refactor:var-comments` | Standardizes @var comments to `/** @var Type $var */`             | 🟢 LOW       | `[path]`, `--dry-run`, `--no-cache`, `--force`                                     |
| `refactor:docblocks`    | Adds a file-level PHPDoc block and class PHPDoc blocks if missing | 🟡 MEDIUM    | `[path]`, `--dry-run`, `--no-cache`, `--force`, `--author`, `--link`, `--category` |
| `refactor:rector`       | Runs Rector for automated refactoring                             | 🔴 HIGH      | `[path]`, `--dry-run`, `--no-cache`, `--force`                                     |
| `refactor:all`          | Runs all enabled refactor commands sequentially                   | 🔴 HIGH      | `[path]`, `--dry-run`, `--no-cache`, `--force`, `--framework`                      |

### 🧠 General

| Command                    | Description                                                                                                                                | Options                                                                 |
| -------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------- |
| `general:generate-command` | Generates a scaffold for a new Symfony Console command                                                                                     | `[path]`, `--dry-run`, `--no-cache`, `--group`, `--cli-name`, `--desc`  |
| `general:generate-docs`    | Scans project controllers and generates a markdown file listing all action routes with their parameters (framework detected automatically) | `[path]`, `--controller-dir`, `--dry-run`, `--no-cache`, `--count-refs` |

### 🧩 Yii Framework Extensions

| Command                        | Description                                                                                                                                                  | Danger Level | Options                             |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------ | ----------------------------------- |
| `yii:all`                      | Runs all Yii-specific Rector refactorings in sequence                                                                                                        | 🟢 LOW       | `[path]`, `--dry-run`, `--force`    |
| `yii:find-shortcuts`           | Converts `Model::find()->where([...])->one()/all()` or `Model::find()->where([...])->limit(1)->one()/all()` into `Model::findOne([...])` or `findAll([...])` | 🟢 LOW       | `[path]`, `--dry-run`, `--force`    |
| `yii:find-one-id`              | Replaces `Model::findOne(['id' => $id])` with `Model::findOne($id)`                                                                                          | 🟢 LOW       | `[path]`, `--dry-run`, `--force`    |
| `yii:find-all-id`              | Replaces `Model::findAll(['id' => $id]) with Model::findAll($id)`                                                                                            | 🟢 LOW       | `[path]`, `--dry-run`, `--force`    |
| `yii:update-shortcut`          | Replaces `Model::find()->where([...])->update([...])` with `Model::updateAll([...], [...])`                                                                  | 🟢 LOW       | `[path]`, `--dry-run`, `--force`    |
| `yii:delete-shortcut`          | Replaces `Model::find()->where([...])->delete()` with `Model::deleteAll([...])`                                                                              | 🟢 LOW       | `[path]`, `--dry-run`, `--force`    |
| `yii:can-helpers`              | Replaces `can()/!can()` chains with `canAny()`, `canAll()`, `cannotAny()`, or `cannotAll()`                                                                  | 🟢 LOW       | `[path]`, `--dry-run`, `--force`    |
| `yii:check-translations`       | Checks Yii::t translations: finds missing and unused keys across all categories                                                                              | N/A          | `[path]`, `--dry-run`, `--lang`     |
| `yii:convert-access-chain`     | Replaces `user->identity->hasAccessChain/hasNoAccessChain` with `user->canAny/cannotAny`                                                                     | N/A          | `[path]`, `--dry-run`, `--no-cache` |
| `yii:user-findone-to-identity` | Replaces redundant `User::findOne(...)` lookups for current user with `Yii::$app->user->identity`                                                            | N/A          | `[path]`, `--dry-run`, `--no-cache` |

### 🧩 Laravel Framework Extensions

| Command       | Description                                               | Danger Level | Options                          |
| ------------- | --------------------------------------------------------- | ------------ | -------------------------------- |
| `laravel:all` | Runs all Laravel-specific Rector refactorings in sequence | 🟢 LOW       | `[path]`, `--dry-run`, `--force` |

## 📁 Configuration

Configuration is defined in PHP via `config.php`, allowing you to enable/disable commands or set options per tool. Example:

The default group names are provided as enum cases in `Vix\Syntra\Enums\CommandGroup`.

```php
use Vix\Syntra\Enums\CommandGroup;

return [
    CommandGroup::REFACTOR->value => [
        PhpCsFixerRefactorer::class => [
            'enabled' => true,
            'config' => __DIR__ . '/config/php_cs_fixer.php',
        ],
    ],
    CommandGroup::YII->value => [
        YiiFindShortcutsCommand::class => true,
    ],
];
```

The PHPStan health check reads from `config/phpstan.neon` by default, so tweak that file to customize analysis settings.

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
    vendor/bin/syntra health:all

    # Or individually
    vendor/bin/syntra health:composer
    vendor/bin/syntra health:phpstan
    vendor/bin/syntra health:phpunit
    vendor/bin/syntra health:security
    ```

2. **Code Analysis Before Refactoring**:

    ```bash
    vendor/bin/syntra analyze:find-todos
    vendor/bin/syntra analyze:find-debug-calls
    vendor/bin/syntra analyze:find-long-methods
    vendor/bin/syntra analyze:find-typos
    # Aspell must be installed
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
    vendor/bin/syntra yii:find-one-id --dry-run
    vendor/bin/syntra yii:check-translations
    ```

### Integration with CI/CD

Add to your CI pipeline:

```yaml
# .github/workflows/syntra.yml
- name: Run Syntra Health Checks
  run: |
      vendor/bin/syntra health:all
      vendor/bin/syntra analyze:find-debug-calls
      vendor/bin/syntra analyze:find-typos
      # install aspell via your package manager before running

# Fail the pipeline on warnings
      vendor/bin/syntra health:composer --ci
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
