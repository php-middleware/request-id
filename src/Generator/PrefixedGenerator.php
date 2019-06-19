<?php

namespace PhpMiddleware\RequestId\Generator;

final class PrefixedGenerator implements GeneratorInterface
{
    private $prefix;
    private $generator;

    public function __construct(string $prefix, GeneratorInterface $generator)
    {
        $this->prefix = $prefix;
        $this->generator = $generator;
    }

    public function generateRequestId(): string
    {
        return $this->prefix . $this->generator->generateRequestId();
    }
}
