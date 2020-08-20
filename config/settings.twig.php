<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'view' => function() {
            return Twig::create(__DIR__ . '/../templates',
                ['cache' => __DIR__ . '/../cache']);
        }
    ]);
};