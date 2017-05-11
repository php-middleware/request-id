<?php

namespace PhpMiddlewareTestTest\RequestId\Generator;

use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\Generator\Md5Generator;
use PHPUnit\Framework\TestCase;

class Md5GeneratorTest extends TestCase
{
    protected $generator;

    protected function setUp()
    {
        $decoratedGenerator = $this->getMock(GeneratorInterface::class);
        $decoratedGenerator->method('generateRequestId')->willReturn('boo');

        $this->generator = new Md5Generator($decoratedGenerator);
    }

    public function testGetHashFromGeneratedValue()
    {
        $result = $this->generator->generateRequestId();

        $this->assertSame('ae3e83e2fab3a7d8683d8eefabd1e74d', $result);
    }
}
