<?php

declare(strict_types=1);

namespace ArpPhpTest\Modules;

use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

final class ModuleDiscovery
{
    public function __construct(private readonly string $modulesDirectory) {}

    /** @return array<string, ModuleManifest> */
    public function discover(): array
    {
        if (!is_dir($this->modulesDirectory)) {
            throw new InvalidArgumentException("Modules directory not found: {$this->modulesDirectory}");
        }
        $result = [];
        foreach (new \DirectoryIterator($this->modulesDirectory) as $item) {
            if ($item->isDot() || $item->isLink() || !$item->isDir()) {
                continue;
            }
            $folder = $item->getPathname();
            $manifest = $folder . DIRECTORY_SEPARATOR . 'module.yaml';
            if (!is_file($manifest)) {
                continue;
            }
            $data = Yaml::parseFile($manifest);
            if (!is_array($data) || array_is_list($data)) {
                throw new InvalidArgumentException("Invalid YAML manifest: {$manifest}");
            }
            $module = ModuleManifest::fromArray($data, $folder);
            if (isset($result[$module->id])) {
                throw new InvalidArgumentException("Duplicate module id: {$module->id}");
            }
            $result[$module->id] = $module;
        }
        ksort($result);
        return $result;
    }
}
