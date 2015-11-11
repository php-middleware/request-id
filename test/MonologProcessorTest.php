<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\MonologProcessor;
use PhpMiddleware\RequestId\RequestIdProviderInterface;

class MonologProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $processor;

    protected function setUp()
    {
        $requestIdProvider = $this->getMock(RequestIdProviderInterface::class);
        $requestIdProvider->expects($this->once())->method('getRequestId')->willReturn('boo');

        $this->processor = new MonologProcessor($requestIdProvider);
    }

    public function testIsRequestIdInRecord()
    {
        $record = ['extra' => []];

        $newRecord = call_user_func($this->processor, $record);

        $this->assertArrayHasKey(MonologProcessor::KEY, $newRecord['extra']);
        $this->assertSame('boo', $newRecord['extra'][MonologProcessor::KEY]);
    }
}
