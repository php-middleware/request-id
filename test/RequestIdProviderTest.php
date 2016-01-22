<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\Exception\InvalidRequestId;
use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use PhpMiddleware\RequestId\RequestIdMiddleware;
use PhpMiddleware\RequestId\RequestIdProvider;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RequestIdProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $generator;

    protected function setUp()
    {
        $this->generator = $this->getMock(GeneratorInterface::class);
    }

    public function testGenerateIdBecauseNotExistInHeader()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');
        $request = new ServerRequest();

        $provider = new RequestIdProvider($request, $this->generator);
        $requestId = $provider->getRequestId();

        $this->assertSame('123456789',$requestId);
    }

    /**
     * @dataProvider provideEmptyRequestIdValues
     */
    public function testTryToGenerateEmptyRequestId($emptyValue)
    {
        $this->setExpectedException(InvalidRequestId::class);

        $this->generator->expects($this->once())->method('generateRequestId')->willReturn($emptyValue);

        $request = new ServerRequest();
        $provider = new RequestIdProvider($request, $this->generator);

        $provider->getRequestId();
    }

    public function provideEmptyRequestIdValues()
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

        $request = new ServerRequest();
        $provider = new RequestIdProvider($request, $this->generator);

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
        $this->setExpectedException(MissingRequestId::class);
        $this->generator->expects($this->never())->method('generateRequestId');
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdProvider::DEFAULT_REQUEST_HEADER => '']);

        $provider = new RequestIdProvider($request, $this->generator);
        $provider->getRequestId();
    }

    public function testOverridePolicyAllowOverride()
    {
        $this->generator->method('generateRequestId')->willReturn('123456789');

        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdProvider::DEFAULT_REQUEST_HEADER => '987654321']);

        $policy = $this->getMock(OverridePolicyInterface::class);
        $policy->method('isAllowToOverride')->with($request)->willReturn(true);

        $provider = new RequestIdProvider($request, $this->generator, $policy);
        $requestId = $provider->getRequestId();

        $this->assertSame('987654321', $requestId);
    }

    public function testOverridePolicyDisallowOverride()
    {
        $this->generator->method('generateRequestId')->willReturn('123456789');
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdProvider::DEFAULT_REQUEST_HEADER => '987654321']);

        $policy = $this->getMock(OverridePolicyInterface::class);
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
