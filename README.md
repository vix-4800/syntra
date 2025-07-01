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

## Usage

Run Syntra commands using the CLI:

```bash
vendor/bin/syntra [command] [options]
```

Example:

```bash
vendor/bin/syntra health:project
```

## ⚙️ Commands

### 🔍 Analyze

| Command                     | Description                                                          |
| --------------------------- | -------------------------------------------------------------------- |
| `analyze:find-debug-calls`  | Finds debug calls like `var_dump()`, `dd()`, `ray()` and similar.    |
| `analyze:find-long-methods` | Detects methods or functions that are too long and need refactoring. |
| `analyze:find-todos`        | Scans code for `TODO`, `FIXME`, `@deprecated`, `@todo`, etc.         |

### ⚙️ Health

| Command          | Description                                               |
| ---------------- | --------------------------------------------------------- |
| `health:project` | Runs Composer, PHPStan, PHPUnit and other project checks. |

### 🔧 Refactor

| Command                 | Description                                                          |
| ----------------------- | -------------------------------------------------------------------- |
| `refactor:cs-fixer`     | Runs PHP-CS-Fixer using project config.                              |
| `refactor:docblocks`    | Adds missing file/class-level PHPDoc blocks.                         |
| `refactor:imports`      | Sorts and standardizes import and docblock order.                    |
| `refactor:rector`       | Executes Rector-based code transformations.                          |
| `refactor:var-comments` | Normalizes `@var` comments to proper `/** @var Type $var */` format. |

### 🧠 General

| Command                    | Description                                                |
| -------------------------- | ---------------------------------------------------------- |
| `general:generate-command` | Generates a new Symfony-style command from stub templates. |

### 🧩 Yii-Specific

| Command               | Description                                                         |
| --------------------- | ------------------------------------------------------------------- |
| `yii:can-helpers`     | Replaces `can()`/`!can()` logic with `canAny()`, `canAll()`, etc.   |
| `yii:delete-shortcut` | Converts `Model::find()->where()->delete()` → `Model::deleteAll()`. |
| `yii:find-id`         | Converts `Model::findOne(['id' => $id])` → `Model::findOne($id)`.   |
| `yii:find-shortcuts`  | Refactors `find()->where()->one()/all()` → `findOne()/findAll()`.   |
| `yii:update-shortcut` | Converts `find()->where()->update()` → `updateAll()`.               |

## 📁 Configuration

Configuration is defined in PHP via `SyntraConfig`, allowing you to enable/disable commands or set options per tool. Example:

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

## 🤝 Contributing

Feel free to fork and contribute your own health checks, refactorers, or extensions via pull requests!

## 📄 License

Syntra is open-source software licensed under the [MIT License](LICENSE).
