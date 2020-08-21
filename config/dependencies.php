<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        'view' => function() {
            return Twig::create(__DIR__ . '/../templates',
                ['cache' => __DIR__ . '/../cache']);
        }
        // 'db' => \Doctrine\DBAL\DriverManager::getConnection(
        //     array(
        //         'dbname' => 'grapevine',
        //         'user' => 'user',
        //         'password' => 'secret',
        //         'host' => 'localhost',
        //         'driver' => 'pdo_mysql',
        // ))
    ]);
};