# Test case for Slim 4.2.0 Base path handling
See Slim 4 [Issue #2512](https://github.com/slimphp/Slim/issues/2512) and [Issue #2842](https://github.com/slimphp/Slim/issues/2842) and [PR #2844](https://github.com/slimphp/Slim/pull/2844)


# Installation

Clone repo and install dependencies
```sh
git clone https://github.com/piotr-cz/Slim-4-basepath-issue.git
composer install
```


# Testing instructions

- Start `composer start`
- Open http://localhost:8080/subdir/


# Results for creating `about` route

App->setBasePath | RouteCollector->setBasePath | Base URL                 | RouteParser::urlFor    | RouteParser::relativeUrlFor
:---------------:|:---------------------------:|:-------------------------|:-----------------------|:---------------------------
 ✗               | ✗                           | `localhost:8080`         | `/about`               | `/about`
 ✓️               | ✗                           | `localhost:8080/subdir/` | `/subdir/about`        | `/subdir/about`
 ✗               |️ ✓                           | `localhost:8080`         | `/subdir/about`        | `/about`
 ✓               | ✓                           | `localhost:8080/subdir/` | `/subdir/subdir/about` | `/subdir/about`
 [✓](https://github.com/slimphp/Slim/pull/2844)             | ✗                           | `localhost:8080/subdir/` | `/subdir/about`        | `/about`


# Expected results

When Slim application is run in subdirectory, it should be possible to
- set it's basepath
- create proper routes (absolute/ relative)

```php
$app = AppFactory::create();
$app->setBasePath('/subdir');

$app->get('/', function ($request, $response) { return $response; })
    ->setName('home');

$app->get('/about', function ($request, $response) { return $response; })
    ->setName('about')

$routeParser = $app->getRouteCollector()->getRouteParser();

echo $routeParser->urlFor('home');            // `/subdir/`
echo $routeParser->relativeUrlFor('home');    // ``
echo $routeParser->fullUrlFor($uri, 'home');  // http://localhost:8080/subdir/

echo $routeParser->urlFor('about');           // `/subdir/about`
echo $routeParser->relativeUrlFor('about');   // `about`
echo $routeParser->fullUrlFor($uri, 'about'); // http://localhost:8080/subdir/about
```
