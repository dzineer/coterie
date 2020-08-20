<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'db' => \Doctrine\DBAL\DriverManager::getConnection(
                array(
                    'dbname' => 'grapevine',
                    'user' => 'user',
                    'password' => 'secret',
                    'host' => 'localhost',
                    'driver' => 'pdo_mysql',
        ))
    ]);
};
//..

