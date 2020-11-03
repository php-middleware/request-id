<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\Exception\InvalidRequestId;
use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use PhpMiddleware\RequestId\RequestIdProvider;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class RequestIdProviderTest extends TestCase
{
    protected $generator;

    protected function setUp(): void
    {
        $this->generator = $this->createMock(GeneratorInterface::class);
    }

    public function testGenerateIdBecauseNotExistInHeader()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');
        $request = new ServerRequest();

        $provider = new RequestIdProvider($request, $this->generator);
        $requestId = $provider->getRequestId();

        $this->assertSame('123456789',$requestId);
    }

    public function testTryToGenerateEmptyRequestId()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('');

        $request = new ServerRequest();
        $provider = new RequestIdProvider($request, $this->generator);

        $this->expectException(InvalidRequestId::class);

        $provider->getRequestId();
    }

    public function testDisallowOverrideButHeaderExists()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdProvider::DEFAULT_REQUEST_HEADER => '987654321']);

        $provider = new RequestIdProvider($request, $this->generator, false);
        $requestId = $provider->getRequestId();

        $this->assertSame('123456789', $requestId);
    }

    public function testDoNotGenerateBecouseHeaderExists()
    {
        $this->generator->expects($this->never())->method('generateRequestId');
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdProvider::DEFAULT_REQUEST_HEADER => '987654321']);

        $provider = new RequestIdProvider($request, $this->generator);
        $requestId = $provider->getRequestId();

        $this->assertSame('987654321', $requestId);
    }

    public function testDoNotGenerateBecauseHeaderExistsButEmpty()
    {
        $this->generator->expects($this->never())->method('generateRequestId');
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdProvider::DEFAULT_REQUEST_HEADER => '']);

        $provider = new RequestIdProvider($request, $this->generator);

        $this->expectException(MissingRequestId::class);

        $provider->getRequestId();
    }

    public function testOverridePolicyAllowOverride()
    {
        $this->generator->method('generateRequestId')->willReturn('123456789');

        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdProvider::DEFAULT_REQUEST_HEADER => '987654321']);

        $policy = $this->createMock(OverridePolicyInterface::class);
        $policy->method('isAllowToOverride')->with($request)->willReturn(true);

        $provider = new RequestIdProvider($request, $this->generator, $policy);
        $requestId = $provider->getRequestId();

        $this->assertSame('987654321', $requestId);
    }

    public function testOverridePolicyDisallowOverride()
    {
        $this->generator->method('generateRequestId')->willReturn('123456789');
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdProvider::DEFAULT_REQUEST_HEADER => '987654321']);

        $policy = $this->createMock(OverridePolicyInterface::class);
        $policy->method('isAllowToOverride')->with($request)->willReturn(false);

        $provider = new RequestIdProvider($request, $this->generator, $policy);
        $requestId = $provider->getRequestId();

        $this->assertSame('123456789', $requestId);
    }

    public function testUseCachedValue()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');
        $request = new ServerRequest();

        $provider = new RequestIdProvider($request, $this->generator, false);
        $requestId = $provider->getRequestId();

        $this->assertSame('123456789', $requestId);

        $requestIdAfterSecondCall = $provider->getRequestId();

        $this->assertSame('123456789', $requestIdAfterSecondCall);
    }
}
