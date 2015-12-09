<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\Exception\InvalidRequestId;
use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use PhpMiddleware\RequestId\RequestIdMiddleware;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RequestIdMiddlewareTest extends PHPUnit_Framework_TestCase
{
    protected $generator;

    protected function setUp()
    {
        $this->generator = $this->getMock(GeneratorInterface::class);
    }

    public function testGenerateIdAndEmmitToResponse()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest();
        $response = new Response();
        $calledOut = false;

        $outFunction = function (ServerRequestInterface $request, $response) use (&$calledOut) {
            $calledOut = true;

            $this->assertSame('123456789', $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME));

            return $response;
        };

        $result = call_user_func($middleware, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertNotSame($response, $result);
        $this->assertEquals('123456789', $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
        $this->assertSame('123456789', $middleware->getRequestId());
    }

    public function testGenerateIdAndNotEmmitToResponse()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');

        $middleware = new RequestIdMiddleware($this->generator, true, null);
        $request = new ServerRequest();
        $response = new Response();
        $calledOut = false;

        $outFunction = function ($request, $response) use (&$calledOut) {
            $calledOut = true;

            $this->assertSame('123456789', $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME));

            return $response;
        };

        $result = call_user_func($middleware, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertSame($response, $result);
        $this->assertEquals(null, $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
        $this->assertSame('123456789', $middleware->getRequestId());
    }

    public function testTryToGetRequestIdBeforeRunMiddleware()
    {
        $this->setExpectedException(MissingRequestId::class);

        $middleware = new RequestIdMiddleware($this->generator);
        $middleware->getRequestId();
    }

    /**
     * @dataProvider dataproviderEmptyRequestIdValues
     */
    public function testTryToGenerateEmptyRequestId($emptyValue)
    {
        $this->setExpectedException(InvalidRequestId::class);

        $this->generator->expects($this->once())->method('generateRequestId')->willReturn($emptyValue);

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest();
        $response = new Response();

        $outFunction = function () {};

        call_user_func($middleware, $request, $response, $outFunction);
    }

    public function dataproviderEmptyRequestIdValues()
    {
        return [
            [''],
            [null],
        ];
    }

    public function testTryToGenerateNotStringRequestId()
    {
        $this->setExpectedException(InvalidRequestId::class);

        $this->generator->expects($this->once())->method('generateRequestId')->willReturn(1);

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest();
        $response = new Response();

        call_user_func($middleware, $request, $response, function () {});
    }

    public function testDisallowOverrideButHeaderExists()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');

        $middleware = new RequestIdMiddleware($this->generator, false);
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID => '987654321']);
        $response = new Response();
        $calledOut = false;

        $outFunction = function ($request, $response) use (&$calledOut) {
            $calledOut = true;

            $this->assertSame('123456789', $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME));

            return $response;
        };

        $result = call_user_func($middleware, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertNotSame($response, $result);
        $this->assertEquals('123456789', $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
        $this->assertSame('123456789', $middleware->getRequestId());
    }

    public function testDontGenerateBecouseHeaderExists()
    {
        $this->generator->expects($this->never())->method('generateRequestId');

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID => '987654321']);
        $response = new Response();
        $calledOut = false;

        $outFunction = function ($request, $response) use (&$calledOut) {
            $calledOut = true;

            $this->assertSame('987654321', $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME));

            return $response;
        };

        $result = call_user_func($middleware, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertNotSame($response, $result);
        $this->assertEquals('987654321', $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
        $this->assertSame('987654321', $middleware->getRequestId());
    }

    public function testDontGenerateBecouseHeaderExistsButEmpty()
    {
        $this->setExpectedException(MissingRequestId::class);
        $this->generator->expects($this->never())->method('generateRequestId');

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID => '']);
        $response = new Response();

        call_user_func($middleware, $request, $response, function(){});
    }

    public function testOverridePolicyAllowOverride()
    {
        $this->generator->method('generateRequestId')->willReturn('123456789');

        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID => '987654321']);

        $policy = $this->getMock(OverridePolicyInterface::class);
        $policy->method('isAllowToOverride')->willReturn(true);
        $response = new Response();

        $middleware = new RequestIdMiddleware($this->generator, $policy);
        $result = call_user_func($middleware, $request, $response, function ($request, $response) {
            return $response;
        });

        $this->assertSame('987654321', $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
    }

    public function testOverridePolicyDisallowOverride()
    {
        $this->generator->method('generateRequestId')->willReturn('123456789');

        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID => '987654321']);

        $policy = $this->getMock(OverridePolicyInterface::class);
        $policy->method('isAllowToOverride')->willReturn(false);
        $response = new Response();

        $middleware = new RequestIdMiddleware($this->generator, $policy);
        $result = call_user_func($middleware, $request, $response, function ($request, $response) {
            return $response;
        });

        $this->assertSame('123456789', $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
    }
}
