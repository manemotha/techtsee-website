<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use DI\Container;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

// Create Container and App
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

// Base path is used by html links/routes ({{basepath}}/admin/)
// Ensure this path is a valid path
$app->setBasePath($_ENV['URL_BASENAME']);

// Register Twig
$twig = Twig::create([
    __DIR__ . '/templates',
    ], ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// Include routes
(require __DIR__ . '/src/routes.php')($app);

$app->run();
