<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\MonologProcessor;
use PhpMiddleware\RequestId\RequestIdProviderInterface;
use PHPUnit\Framework\TestCase;

class MonologProcessorTest extends TestCase
{
    protected $processor;
    private $requestIdProvider;

    public function testIsRequestIdInRecord()
    {
        $this->requestIdProvider->expects($this->once())->method('getRequestId')->willReturn('boo');
        $record = ['extra' => []];

        $newRecord = call_user_func($this->processor, $record);

        $this->assertArrayHasKey(MonologProcessor::KEY, $newRecord['extra']);
        $this->assertSame('boo', $newRecord['extra'][MonologProcessor::KEY]);
    }

    public function testIsMissingRequestIdExceptionHandledProperly()
    {
        $this->requestIdProvider->expects($this->once())->method('getRequestId')->willThrowException(new MissingRequestId());
        $record = ['extra' => []];

        $newRecord = call_user_func($this->processor, $record);

        $this->assertArrayHasKey(MonologProcessor::KEY, $newRecord['extra']);
        $this->assertNull($newRecord['extra'][MonologProcessor::KEY]);
    }

    protected function setUp(): void
    {
        $this->requestIdProvider = $this->createMock(RequestIdProviderInterface::class);
        $this->processor         = new MonologProcessor($this->requestIdProvider);
    }
}
