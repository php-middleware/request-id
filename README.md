# request-id middleware [![Build Status](https://travis-ci.org/php-middleware/request-id.svg?branch=master)](https://travis-ci.org/php-middleware/request-id)

PSR-7 request id middleware

This middleware provide framework-agnostic possibility to generate and add to request/response's header request id.

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

* `PhpMiddleware\RequestId\Generator\GeneratorInterface` `$generator` - generator implementation (required)
* `bool` `$allowOverride` (default `true`) - if `true` and request id header exists in incoming request, then value from request header will be used in middleware, using generator will be avoid
* `string` `$responseHeader` (default `X-Request-Id`) - request id will be added to response as header with given name. If it's not string request id will be not added to response
* `string` `$requestHeader` (default `X-Request-Id`) - request header name

How to get request id in my application?

* Middleware implements `RequestIdProviderInterface`, so you are able to use `getRequestId()` method,
* from `request-id` attribute in `ServerRequest` object (`$request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME)`).

### Request Id generators

To generate request id you need to use implementation of `PhpMiddleware\RequestId\Generator\GeneratorInterface`. There are predefined generators in `PhpMiddleware\RequestId\Generator\` namespace:

#### `PhpUniqidGenerator`

Simple generator using [uniqid](http://php.net/manual/en/function.uniqid.php) function.

#### `RhumsaaUuid4Generator`

[UUID](https://tools.ietf.org/html/rfc4122)4 implementations of [Rhumsaa\Uuid](https://github.com/ramsey/uuid). To use it you need to add `ramsey/uuid` dependency to your `composer.json`.

## It's just works with any modern php framework!

Middleware tested on:
* [Expressive](https://github.com/zendframework/zend-expressive)

Middleware should works with:
* [Slim 3.x](https://github.com/slimphp/Slim)

And any other modern framework [supported middlewares and PSR-7](https://mwop.net/blog/2015-01-08-on-http-middleware-and-psr-7.html).
