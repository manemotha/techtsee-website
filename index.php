<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use DI\Container;
use Dotenv\Dotenv;
use Slim\Psr7\Response;
use Slim\Exception\HttpNotFoundException;

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
// TODO: catch LoaderError exception
$twig = Twig::create([
    __DIR__ . '/templates',
    ], ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// Templates route
(require __DIR__ . '/src/templates.php')($app);

// Internal service routes
(require __DIR__ . '/src/endpoints/user.php')($app);

// Handle exceptions for unknown/unhandled endpoints
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function ($request) {
        global $twig;
        return $twig->render(new Response(), '/error_handlers/404.twig', [
            'URL_BASENAME'=>$_ENV['URL_BASENAME'],
            'request'=>$request
        ]);
    }
);

// Run application
$app->run();
