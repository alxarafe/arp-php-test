<?php

declare(strict_types=1);

use ArpPhpTest\Modules\ModuleContext;

// Application-specific integration seam. ARP dictionary registration comes later.
return static function (ModuleContext $context): void {
    $context->registerCapability('catalog.products', ['table' => 'catalog_products']);
};
