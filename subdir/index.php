<?php
/**
 * Front controller
 */
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Include vendor libraries
require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Set base path without any auto-detections
$basePath = '/subdir';

// Handle base path
$request = \Slim\Factory\ServerRequestCreatorFactory::create()->createServerRequestFromGlobals();
$app->setBasePath($basePath); // Required to resolve current route by router
// $app->getRouteCollector()->setBasePath($basePath); // This soubles basepath in RouteParser methods (routes already include basePath)


// Response renderer
$routeParser = $app->getRouteCollector()->getRouteParser();

$responseRenderer = function (Request $request, Response $response, string $routeName) use ($routeParser): Response {
    $uri = $request->getUri();

    // ATM need to create Base URI manually, see https://github.com/slimphp/Slim/issues/2837
    $baseHref = $routeParser->fullUrlFor($uri, 'home');

    $response->getBody()->write(<<<HTML
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8" />
                <base href="{$baseHref}" />
            </head>
            <body>
                <h1>{$routeName}</h1>
                <fieldset>
                    <legend>absolute (<code>RouteParser::urlFor</code>)</legend>
                    <a href="{$routeParser->urlFor('home')}">home</a>
                    <a href="{$routeParser->urlFor('about')}">about</a>
                </fieldset>
                <fieldset>
                    <legend>relative (<code>RouteParser::relativeUrlFor</code>)</legend>
                    <a href=".{$routeParser->relativeUrlFor('home')}">home</a>
                    <a href=".{$routeParser->relativeUrlFor('about')}">about</a>
                </fieldset>
                <fieldset>
                    <legend>full (<code>RouteParser::fullUrlFor</code>)</legend>
                    <a href="{$routeParser->fullUrlFor($uri, 'home')}">home</a>
                    <a href="{$routeParser->fullUrlFor($uri, 'about')}">about</a>
                </fieldset>
                <script>
                  // Show href as a title
                  [...document.querySelectorAll('a[href]')].forEach(el => el.title = el.getAttribute('href'))
                </script>
            </body>
        </html>
HTML
    );

    return $response;
};


// Routes
$app->get('/', function (Request $request, Response $response) use ($responseRenderer): Response {
    return $responseRenderer($request, $response, 'home');
})->setName('home');

$app->get('/about', function (Request $request, Response $response) use ($responseRenderer): Response {
    return $responseRenderer($request, $response, 'about');
})->setName('about');


// Display errors
$app->addErrorMiddleware(true, false, false);


// Add Routing Middleware
// $app->addRoutingMiddleware();


// Run application
$app->run($request);
