<?php

declare(strict_types=1);

namespace ArpPhpTest\Tests\Integration;

use ArpPhpTest\Infrastructure\Database;
use ArpPhpTest\Modules\ModuleManager;
use PHPUnit\Framework\TestCase;

final class ModuleIntegrationTest extends TestCase
{
    public function testInstallEnableDisableAndReenable(): void
    {
        // Dedicated integration DB only. It is never the application DB.
        $db = Database::connect('arp_php_test_integration');
        $db->exec('DROP TABLE IF EXISTS catalog_stock_references');
        $db->exec('DROP TABLE IF EXISTS catalog_products');
        $db->exec('DROP TABLE IF EXISTS app_modules');
        $manager = new ModuleManager($db, dirname(__DIR__, 2) . '/modules');
        $manager->initialize();

        self::assertSame('discovered', $manager->list()[0]['state']);
        $manager->install('catalog');
        self::assertSame('installed', $manager->list()[0]['state']);
        self::assertSame([], $manager->loadOrder());
        $manager->enable('catalog');
        self::assertSame(['catalog'], $manager->loadOrder());
        self::assertArrayHasKey('catalog.products', $manager->boot()['catalog']);
        $manager->disable('catalog');
        self::assertSame([], $manager->boot());
        $manager->enable('catalog');
        self::assertSame(['catalog'], $manager->loadOrder());
    }
}
