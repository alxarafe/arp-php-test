<?php

declare(strict_types=1);

namespace ArpPhpTest\Tests\Unit;

use ArpPhpTest\Modules\ModuleManifest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ModuleManifestTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/arp-module-test-' . bin2hex(random_bytes(8)) . '/catalog';
        mkdir($this->dir, 0700, true);
        file_put_contents($this->dir . '/bootstrap.php', '<?php return static function (): void {};');
    }

    protected function tearDown(): void
    {
        unlink($this->dir . '/bootstrap.php');
        rmdir($this->dir);
        rmdir(dirname($this->dir));
    }

    public function testManifestAcceptsValidModule(): void
    {
        $module = ModuleManifest::fromArray([
            'id' => 'catalog', 'name' => 'Catalog', 'version' => '0.1.0',
            'entrypoint' => 'bootstrap.php', 'dependencies' => [],
        ], $this->dir);
        self::assertSame('catalog', $module->id);
    }

    public function testManifestRejectsTraversalEntrypoint(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ModuleManifest::fromArray([
            'id' => 'catalog', 'name' => 'Catalog', 'version' => '0.1.0',
            'entrypoint' => '../outside.php',
        ], $this->dir);
    }

    public function testManifestRejectsUnknownConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ModuleManifest::fromArray([
            'id' => 'catalog', 'name' => 'Catalog', 'version' => '0.1.0',
            'entrypoint' => 'bootstrap.php', 'unexpected' => 'value',
        ], $this->dir);
    }
}
