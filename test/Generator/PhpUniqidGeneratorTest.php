<?php

namespace PhpMiddlewareTestTest\RequestId\Generator;

use PhpMiddleware\RequestId\Generator\PhpUniqidGenerator;
use PHPUnit\Framework\TestCase;

class PhpUniqidGeneratorTest extends TestCase
{
    protected $generator;

    protected function setUp()
    {
        $this->generator = new PhpUniqidGenerator();
    }

    public function testGetHashFromGeneratedValue()
    {
        $result = $this->generator->generateRequestId();

        $this->assertNotEmpty($result);
    }
}
