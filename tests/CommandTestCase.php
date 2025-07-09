<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Cache;
use Vix\Syntra\Facades\Project;

abstract class CommandTestCase extends TestCase
{
    protected Application $app;
    protected string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/syntra_' . uniqid();
        mkdir($this->dir, 0777, true);

        $this->app = new Application();
        Cache::clearAll();
        Config::setContainer($this->app->getContainer());
        Project::setRootPath($this->dir);
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->dir);
        parent::tearDown();
    }

    protected function runCommand(string $name, array $input = []): CommandTester
    {
        $command = $this->app->find($name);
        $tester = new CommandTester($command);
        $tester->execute($input);
        return $tester;
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}
