<?php

declare(strict_types=1);

namespace ArpPhpTest;

use ArpPhpTest\Infrastructure\Database;
use ArpPhpTest\Modules\ModuleManager;

final class Application
{
    public static function modules(?string $database = null): ModuleManager
    {
        $root = dirname(__DIR__);
        $directory = getenv('MODULES_DIR') ?: $root . '/modules';
        $manager = new ModuleManager(Database::connect($database), $directory);
        $manager->initialize();
        return $manager;
    }
}
