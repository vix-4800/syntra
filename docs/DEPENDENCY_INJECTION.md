# Dependency Injection in Syntra

Syntra now uses a dependency injection container to manage services and their dependencies, following SOLID principles and best practices.

## Overview

The DI container provides:

-   Automatic constructor dependency injection
-   Singleton and transient service lifetimes
-   Service providers for organizing registrations
-   PSR-11 compliant container interface
-   Circular dependency detection

## Container Usage

### Basic Service Registration

```php
$container = new Container();

// Register a transient service
$container->bind(SomeService::class, SomeService::class);

// Register a singleton
$container->singleton(ConfigLoader::class, function() {
    return new ConfigLoader();
});

// Register an instance
$configLoader = new ConfigLoader();
$container->instance(ConfigLoader::class, $configLoader);
```

### Getting Services

```php
// Get a service (will create if not exists)
$service = $container->get(SomeService::class);

// Make a new instance (always creates new)
$service = $container->make(SomeService::class);

// Check if service exists
if ($container->has(SomeService::class)) {
    // Service is available
}
```

## Service Providers

Service providers organize service registrations:

```php
class MyServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->singleton(MyService::class, function() {
            return new MyService();
        });
    }

    public function boot(ContainerInterface $container): void
    {
        // Boot logic after all services are registered
    }
}
```

## Available Service Providers

### ApplicationServiceProvider

Registers core application services:

-   `ConfigLoader` - Configuration management
-   `ProcessRunner` - Process execution
-   `ExtensionManager` - Extension management
-   `FileHelper` - File operations

### HealthServiceProvider

Provides bindings for health-related commands. You can extend it to register
additional checks such as a security scanner.

### ParserServiceProvider

Registers PHP parsing services:

-   `Parser` - PHP parser (singleton)
-   `NodeTraverser` - AST traverser (transient)
-   `AssignmentInConditionVisitor` - Code analysis visitor
-   `NestedTernaryVisitor` - Code analysis visitor
-   `ReturnThrowVisitor` - Code analysis visitor
-   `parser.traverser_factory` - Factory for configured traversers

## Command Integration

Commands automatically receive dependencies through constructor injection:

```php
class MyCommand extends SyntraCommand
{
    public function __construct(
        ConfigLoader $configLoader,
        ProcessRunner $processRunner,
        ExtensionManager $extensionManager,
        private MyService $myService  // Additional dependency
    ) {
        parent::__construct($configLoader, $processRunner, $extensionManager);
    }
}
```

### Accessing Application Container

Commands can access the application container:

```php
if ($this->getApplication() instanceof \Vix\Syntra\Application) {
    $container = $this->getApplication()->getContainer();
    $service = $container->get(MyService::class);
}
```

## Best Practices

1. **Use interfaces**: Bind interfaces to implementations for better testability
2. **Prefer constructor injection**: Explicitly declare dependencies in constructors
3. **Use service providers**: Organize related service registrations
4. **Singleton vs Transient**: Use singletons for stateless services, transient for stateful
5. **Avoid service locator pattern**: Don't pass the container around

## Benefits

-   **Testability**: Easy to mock dependencies in tests
-   **Flexibility**: Easy to swap implementations
-   **SOLID compliance**: Follows dependency inversion principle
-   **Maintainability**: Clear dependency relationships
-   **Performance**: Efficient service resolution with circular dependency detection

## Migration

The system maintains backwards compatibility. Existing commands will continue to work without modification, but new commands should use dependency injection where possible.
