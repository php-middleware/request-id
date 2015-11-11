# request-id middleware [![Build Status](https://travis-ci.org/php-middleware/request-id.svg?branch=master)](https://travis-ci.org/php-middleware/request-id)

Request Id middleware with PSR-7

This middleware provide framework-agnostic possibility to generate and add to request/response header request id.

## Installation

```json
{
    "require": {
        "php-middleware/request-id": "^2.0.0"
    }
}
```

## Usage

This middleware require in contructor `PhpMiddleware\RequestId\Generator\GeneratorInterface` implementation.

```php
$requestIdMiddleware = new PhpMiddleware\LogHttpMessages\RequestIdMiddleware($generator);

$app = new MiddlewareRunner();
$app->add($requestIdMiddleware);
$app->run($request, $response);
```

All middleware constructor options:

* `PhpMiddleware\RequestId\Generator\GeneratorInterface` `$generator` - generator implementation with method `generateRequestId`
* `bool` `$allowOverride` (default `true`) - if `true` and request id header exists in incoming request, then value from request header will be used in middleware, generator will be avoid
* `bool` `$emmitToResponse` (default `true`) - if `true` request id will be added to response header
* `string` `$headerName` (default `X-Request-Id`) - header name

### How to get request id in my application?

* middleware implements `RequestIdProviderInterface`, so you are able to use `getRequestId` method,
* from `request-id` attibute `ServerRequest` object (`$request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME`)),

## It's just works with any modern php framework!

Middleware tested on:
* [Expressive](https://github.com/zendframework/zend-expressive)

Middleware should works with:
* [Slim 3.x](https://github.com/slimphp/Slim)

And any other modern framework [supported middlewares and PSR-7](https://mwop.net/blog/2015-01-08-on-http-middleware-and-psr-7.html).
