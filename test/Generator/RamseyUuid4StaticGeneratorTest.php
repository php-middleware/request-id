<?php

use PhpMiddleware\RequestId\Generator\RamseyUuid4StaticGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

namespace PhpMiddlewareTestTest\RequestId\Generator;

class RamseyUuid4StaticGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected $generator;


    protected function setUp()
    {
        $this->generator = new RamseyUuid4StaticGenerator();
    }

    public function testGenerateId()
    {
        $uuidString = $this->generator->generateRequestId();

        $uuid = Uuid::fromString($uuidString);

        $this->assertInstanceOf(UuidInterface::class, $uuid);
    }
}
