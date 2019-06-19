<?php

namespace PhpMiddleware\RequestId\Generator;

final class Md5Generator implements GeneratorInterface
{
    private $generator;

    public function __construct(GeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function generateRequestId(): string
    {
        return md5($this->generator->generateRequestId());
    }
}
