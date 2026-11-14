<?php

declare(strict_types=1);

namespace ArpPhpTest\Tests\Unit;

use ArpPhpTest\Modules\ModuleDiscovery;
use PHPUnit\Framework\TestCase;

final class ModuleDiscoveryTest extends TestCase
{
    public function testDiscoverRepositoryCatalog(): void
    {
        $discovered = (new ModuleDiscovery(dirname(__DIR__, 2) . '/modules'))->discover();
        self::assertArrayHasKey('catalog', $discovered);
        self::assertSame('0.1.0', $discovered['catalog']->version);
    }
}
