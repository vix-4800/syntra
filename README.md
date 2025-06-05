# Syntra

**Syntra** is a unified CLI tool for PHP projects, designed to streamline health checks, code refactoring, and framework integrations. It automates routine tasks, enforces coding standards, and provides a modular architecture for extensibility.

## Features

-   **Project Health Checks**: Run diagnostics using tools like Composer, PHPStan, PHPUnit, PHP-CS-Fixer, and security scanners.
-   **Code Refactoring**: Apply automated code transformations using Rector and custom refactorers.
-   **Framework Integrations**: Seamlessly integrate with popular PHP frameworks (planned).
-   **Extension Support**: Easily extend Syntra with custom commands and plugins (planned).

## Installation

```bash
composer require vix/syntra
```

## Usage

Run Syntra commands using the CLI:

```bash
vendor/bin/syntra [command] [options]
```

### Example

```bash
vendor/bin/syntra project:check --path=/path/to/your/project
```

## Configuration

Syntra uses a configuration file (`syntra.yml` or `syntra.json`) located at the root of your project. This file allows you to customize tool settings, paths, and other options.

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request with your improvements.

## License

This project is licensed under the MIT License.
