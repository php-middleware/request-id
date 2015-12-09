<?php

namespace PhpMiddlewareTestTest\RequestId\Generator;

use PHPUnit_Framework_TestCase;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;

class RamseyFactoryUuidGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected $factory;
    protected $uuid;

    protected function setUp()
    {
        $this->factory = $this->getMock(UuidFactoryInterface::class);

        $this->uuid = $this->getMock(UuidInterface::class);
        $this->uuid->method('toString')->willReturn('uuid');
    }

    public function testUuid1Generator()
    {
        $generator = new \PhpMiddleware\RequestId\Generator\RamseyUuid1Generator($this->factory);
        $this->factory->method('uuid1')->willReturn($this->uuid);

        $result = $generator->generateRequestId();

        $this->assertSame('uuid', $result);
    }

    public function testUuid3Generator()
    {
        $generator = new \PhpMiddleware\RequestId\Generator\RamseyUuid3Generator($this->factory, 'ns', 'name');
        $this->factory->method('uuid3')->with('ns', 'name')->willReturn($this->uuid);

        $result = $generator->generateRequestId();

        $this->assertSame('uuid', $result);
    }

    public function testUuid4Generator()
    {
        $generator = new \PhpMiddleware\RequestId\Generator\RamseyUuid4Generator($this->factory);
        $this->factory->method('uuid4')->willReturn($this->uuid);

        $result = $generator->generateRequestId();

        $this->assertSame('uuid', $result);
    }

    public function testUuid5Generator()
    {
        $generator = new \PhpMiddleware\RequestId\Generator\RamseyUuid5Generator($this->factory, 'ns', 'name');
        $this->factory->method('uuid5')->with('ns', 'name')->willReturn($this->uuid);

        $result = $generator->generateRequestId();

        $this->assertSame('uuid', $result);
    }
}
