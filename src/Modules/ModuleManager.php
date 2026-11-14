<?php

declare(strict_types=1);

namespace ArpPhpTest\Modules;

use DomainException;
use PDO;

final class ModuleManager
{
    /** @var array<string, ModuleManifest> */
    private readonly array $discovered;
    private readonly ModuleStateStore $store;

    public function __construct(private readonly PDO $db, string $modulesDirectory)
    {
        $this->discovered = (new ModuleDiscovery($modulesDirectory))->discover();
        $this->store = new ModuleStateStore($db);
    }

    public function initialize(): void
    {
        $this->store->initialize();
    }

    /** @return list<array{id: string, version: string, state: string}> */
    public function list(): array
    {
        $installed = $this->store->installed();
        $rows = [];
        foreach ($this->discovered as $module) {
            $state = $installed[$module->id] ?? null;
            $rows[] = [
                'id' => $module->id,
                'version' => $module->version,
                'state' => $state === null ? 'discovered' : ($state['enabled'] ? 'enabled' : 'installed'),
            ];
        }
        // Report missing installed modules too, without loading arbitrary code.
        foreach ($installed as $id => $state) {
            if (!isset($this->discovered[$id])) {
                $rows[] = ['id' => $id, 'version' => $state['version'], 'state' => 'missing'];
            }
        }
        return $rows;
    }

    public function install(string $id): void
    {
        $module = $this->module($id);
        $states = $this->store->installed();
        if (isset($states[$id])) {
            throw new DomainException("Module already installed: {$id}");
        }
        foreach ($module->dependencies as $dependency) {
            if (!isset($states[$dependency])) {
                throw new DomainException("Install dependency first: {$dependency}");
            }
        }
        $sqlDirectory = $module->directory . '/database/install';
        if (is_dir($sqlDirectory)) {
            $sql = glob($sqlDirectory . '/*.sql') ?: [];
            sort($sql, SORT_STRING);
            foreach ($sql as $script) {
                $contents = file_get_contents($script);
                if ($contents === false) {
                    throw new DomainException("Cannot read install script: {$script}");
                }
                // One SQL statement per file; intentional, avoiding naive SQL splitting.
                $this->db->exec($contents);
            }
        }
        // SQL DDL is not assumed transactional in MariaDB. Scripts must be retry-safe.
        $this->store->insertInstalled($module);
    }

    public function enable(string $id): void
    {
        $module = $this->module($id);
        $states = $this->store->installed();
        if (!isset($states[$id])) {
            throw new DomainException("Install module first: {$id}");
        }
        foreach ($module->dependencies as $dependency) {
            if (!isset($this->discovered[$dependency]) || !($states[$dependency]['enabled'] ?? false)) {
                throw new DomainException("Enable dependency first: {$dependency}");
            }
        }
        $this->store->setEnabled($id, true);
        // Check cycles immediately, even though full boot is deferred.
        try {
            $this->loadOrder();
        } catch (\Throwable $exception) {
            $this->store->setEnabled($id, false);
            throw $exception;
        }
    }

    public function disable(string $id): void
    {
        $this->module($id);
        $states = $this->store->installed();
        if (!isset($states[$id])) {
            throw new DomainException("Module not installed: {$id}");
        }
        foreach ($this->discovered as $other) {
            if (($states[$other->id]['enabled'] ?? false) && in_array($id, $other->dependencies, true)) {
                throw new DomainException("Disable dependent module first: {$other->id}");
            }
        }
        $this->store->setEnabled($id, false);
    }

    /** @return list<string> */
    public function loadOrder(): array
    {
        $states = $this->store->installed();
        $visited = [];
        $visiting = [];
        $ordered = [];
        $visit = function (string $id) use (&$visit, &$visited, &$visiting, &$ordered, $states): void {
            if (isset($visited[$id])) return;
            if (isset($visiting[$id])) throw new DomainException("Cyclic module dependency: {$id}");
            $manifest = $this->module($id);
            $visiting[$id] = true;
            foreach ($manifest->dependencies as $dependency) {
                if (!($states[$dependency]['enabled'] ?? false)) {
                    throw new DomainException("Required dependency not enabled: {$dependency}");
                }
                $visit($dependency);
            }
            unset($visiting[$id]);
            $visited[$id] = true;
            $ordered[] = $id;
        };
        foreach ($states as $id => $state) {
            if ($state['enabled']) $visit($id);
        }
        return $ordered;
    }

    /** @return array<string, array<string, mixed>> */
    public function boot(): array
    {
        $out = [];
        foreach ($this->loadOrder() as $id) {
            $module = $this->module($id);
            $entrypoint = $module->directory . '/' . $module->entrypoint;
            $register = require $entrypoint;
            if (!is_callable($register)) {
                throw new DomainException("Module entrypoint must return a callable: {$id}");
            }
            $context = new ModuleContext($id, $this->db);
            $register($context);
            $out[$id] = $context->capabilities();
        }
        return $out;
    }

    private function module(string $id): ModuleManifest
    {
        return $this->discovered[$id] ?? throw new DomainException("Unknown module: {$id}");
    }
}
