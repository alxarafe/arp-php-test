<?php

declare(strict_types=1);

namespace ArpPhpTest\Modules;

use InvalidArgumentException;

final readonly class ModuleManifest
{
    /** @param list<string> $dependencies */
    public function __construct(
        public string $id,
        public string $name,
        public string $version,
        public string $directory,
        public string $entrypoint,
        public array $dependencies,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data, string $directory): self
    {
        $allowed = ['id', 'name', 'version', 'entrypoint', 'dependencies'];
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException("Unknown module manifest property: {$key}");
            }
        }
        $id = $data['id'] ?? null;
        $name = $data['name'] ?? null;
        $version = $data['version'] ?? null;
        $entrypoint = $data['entrypoint'] ?? null;
        $dependencies = $data['dependencies'] ?? [];
        if (!is_string($id) || !preg_match('/^[a-z][a-z0-9_-]*$/D', $id)) {
            throw new InvalidArgumentException('Invalid module id');
        }
        if (basename($directory) !== $id) {
            throw new InvalidArgumentException("Module id '{$id}' must equal its directory name");
        }
        if (!is_string($name) || trim($name) === '') {
            throw new InvalidArgumentException("Invalid module name for '{$id}'");
        }
        if (!is_string($version) || !preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/D', $version)) {
            throw new InvalidArgumentException("Invalid module version for '{$id}'");
        }
        if (!is_string($entrypoint) || !preg_match('/^[a-zA-Z0-9_-]+\.php$/D', $entrypoint)) {
            throw new InvalidArgumentException("Unsafe module entrypoint for '{$id}'");
        }
        if (!is_file($directory . DIRECTORY_SEPARATOR . $entrypoint)) {
            throw new InvalidArgumentException("Entrypoint missing for '{$id}'");
        }
        if (!is_array($dependencies) || !array_is_list($dependencies)) {
            throw new InvalidArgumentException("Invalid dependencies for '{$id}'");
        }
        foreach ($dependencies as $dependency) {
            if (!is_string($dependency) || !preg_match('/^[a-z][a-z0-9_-]*$/D', $dependency) || $dependency === $id) {
                throw new InvalidArgumentException("Invalid dependency for '{$id}'");
            }
        }
        if (count($dependencies) !== count(array_unique($dependencies))) {
            throw new InvalidArgumentException("Duplicate dependencies for '{$id}'");
        }
        return new self($id, $name, $version, $directory, $entrypoint, $dependencies);
    }
}
