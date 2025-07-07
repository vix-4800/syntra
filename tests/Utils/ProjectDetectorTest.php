<?php

namespace Vix\Syntra\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\Utils\FileHelper;
use Vix\Syntra\Utils\ProjectDetector;

class ProjectDetectorTest extends TestCase
{
    public function testDetectsYiiProject(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/composer.json", json_encode([
            'require' => [
                'yiisoft/yii2' => '*',
            ],
        ]));

        FileHelper::clearCache();

        $detector = new ProjectDetector();
        $this->assertSame(ProjectDetector::TYPE_YII, $detector->detect($dir));

        unlink("$dir/composer.json");
        rmdir($dir);
    }

    public function testDetectsLaravelProject(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/composer.json", json_encode([
            'require' => [
                'laravel/framework' => '*',
            ],
        ]));

        FileHelper::clearCache();

        $detector = new ProjectDetector();
        $this->assertSame(ProjectDetector::TYPE_LARAVEL, $detector->detect($dir));

        unlink("$dir/composer.json");
        rmdir($dir);
    }

    public function testDetectsUnknownProject(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/composer.json", json_encode([
            'require' => [
                'some/package' => '*',
            ],
        ]));

        FileHelper::clearCache();

        $detector = new ProjectDetector();
        $this->assertSame(ProjectDetector::TYPE_UNKNOWN, $detector->detect($dir));

        unlink("$dir/composer.json");
        rmdir($dir);
    }
}
