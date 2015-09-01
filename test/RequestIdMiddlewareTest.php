<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\RequestIdMiddleware;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RequestIdMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    protected $generator;

    protected function setUp()
    {
        $this->generator = $this->getMock(GeneratorInterface::class);
    }

    public function testGenerateIdAndEmmitToResponse()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');

        $requestId = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest();
        $response = new Response();
        $calledOut = false;

        $outFunction = function ($request, $response) use (&$calledOut) {
            $calledOut = true;

            return $response;
        };

        $result = call_user_func($requestId, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertNotSame($response, $result);
        $this->assertEquals('123456789', $result->getHeaderLine(RequestIdMiddleware::HEADER_REQUEST_ID));
    }

    public function testGenerateIdAndNotEmmitToResponse()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');

        $requestId = new RequestIdMiddleware($this->generator, true, false);
        $request = new ServerRequest();
        $response = new Response();
        $calledOut = false;

        $outFunction = function ($request, $response) use (&$calledOut) {
            $calledOut = true;

            return $response;
        };

        $result = call_user_func($requestId, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertSame($response, $result);
        $this->assertEquals(null, $result->getHeaderLine(RequestIdMiddleware::HEADER_REQUEST_ID));
    }

    /**
     * @dataProvider dataproviderEmptyRequestIdValues
     * @expectedException PhpMiddleware\RequestId\Exception\NotGenerated
     */
    public function testTryToGenerateEmptyRequestId($emptyValue)
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn($emptyValue);

        $requestId = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest();
        $response = new Response();

        $outFunction = function () {};

        call_user_func($requestId, $request, $response, $outFunction);
    }

    public function dataproviderEmptyRequestIdValues()
    {
        return [
            [''],
            [null],
        ];
    }
}
