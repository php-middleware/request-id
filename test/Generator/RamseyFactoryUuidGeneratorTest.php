<?php

namespace PhpMiddlewareTestTest\RequestId\Generator;

use PhpMiddleware\RequestId\Generator\RamseyUuid1Generator;
use PhpMiddleware\RequestId\Generator\RamseyUuid3Generator;
use PhpMiddleware\RequestId\Generator\RamseyUuid4Generator;
use PhpMiddleware\RequestId\Generator\RamseyUuid5Generator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;

class RamseyFactoryUuidGeneratorTest extends TestCase
{
    protected $factory;
    protected $uuid;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(UuidFactoryInterface::class);

        $this->uuid = $this->createMock(UuidInterface::class);
        $this->uuid->method('toString')->willReturn('uuid');
    }

    public function testUuid1Generator()
    {
        $generator = new RamseyUuid1Generator($this->factory);
        $this->factory->method('uuid1')->willReturn($this->uuid);

        $result = $generator->generateRequestId();

        $this->assertSame('uuid', $result);
    }

    public function testUuid3Generator()
    {
        $generator = new RamseyUuid3Generator($this->factory, 'ns', 'name');
        $this->factory->method('uuid3')->with('ns', 'name')->willReturn($this->uuid);

        $result = $generator->generateRequestId();

        $this->assertSame('uuid', $result);
    }

    public function testUuid4Generator()
    {
        $generator = new RamseyUuid4Generator($this->factory);
        $this->factory->method('uuid4')->willReturn($this->uuid);

        $result = $generator->generateRequestId();

        $this->assertSame('uuid', $result);
    }

    public function testUuid5Generator()
    {
        $generator = new RamseyUuid5Generator($this->factory, 'ns', 'name');
        $this->factory->method('uuid5')->with('ns', 'name')->willReturn($this->uuid);

        $result = $generator->generateRequestId();

        $this->assertSame('uuid', $result);
    }
}
