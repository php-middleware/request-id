<?php

namespace PhpMiddlewareTestTest\RequestId\Generator;

use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\Generator\PrefixedGenerator;
use PHPUnit\Framework\TestCase;

class PrefixedGeneratorTest extends TestCase
{
    protected $decoratedGenerator;

    protected function setUp()
    {
        $this->decoratedGenerator = $this->getMock(GeneratorInterface::class);
        $this->decoratedGenerator->method('generateRequestId')->willReturn('boo');
    }

    public function testGetHashFromGeneratedValue()
    {
        $result = $this->getGenerator('foo_')->generateRequestId();

        $this->assertSame('foo_boo', $result);
    }

    public function getGenerator($prefix)
    {
        return new PrefixedGenerator($prefix, $this->decoratedGenerator);
    }
}