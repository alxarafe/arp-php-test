<?php

declare(strict_types=1);

namespace ArpPhpTest\Modules;

use PDO;

final class ModuleContext
{
    /** @var array<string, mixed> */
    private array $capabilities = [];

    public function __construct(public readonly string $moduleId, public readonly PDO $db) {}

    public function registerCapability(string $name, mixed $value): void
    {
        if (array_key_exists($name, $this->capabilities)) {
            throw new \LogicException("Duplicate capability: {$name}");
        }
        $this->capabilities[$name] = $value;
    }

    /** @return array<string, mixed> */
    public function capabilities(): array
    {
        return $this->capabilities;
    }
}
