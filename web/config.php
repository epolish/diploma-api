<?php

define('APP_DIR', dirname(__DIR__) . '/app/');
define('APP_AUTOLOAD', dirname(__DIR__) . '/vendor/autoload.php');

require_once APP_AUTOLOAD;

use Silex\Application;
use Symfony\Component\Config\FileLocator;
use ExpertSystem\Handler\ResponseHandler;
use ExpertSystem\Handler\ExceptionHandler;
use Lokhman\Silex\Provider\ConfigServiceProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$app = new Application();
$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(APP_DIR));

$loader->load('di.yaml');

$app->register(new ResponseHandler());
$app->register(new ExceptionHandler());
$app->register(new ConfigServiceProvider(), [
    'config.dir' => APP_DIR . '/config/',
]);

$app['debug'] = $app['config']['debug'];

$container->setParameter('db.options', $app['config']['db.options']);

$manager = $container->get('db_manager');
