<?php

namespace PhpMiddleware\RequestId\Generator;

final class PrefixedGenerator implements GeneratorInterface
{
    private $prefix;
    private $generator;

    /**
     * @param string $prefix
     * @param GeneratorInterface $generator
     */
    public function __construct($prefix, GeneratorInterface $generator)
    {
        $this->prefix = (string) $prefix;
        $this->generator = $generator;
    }

    /**
     * @return string
     */
    public function generateRequestId()
    {
        return $this->prefix . $this->generator->generateRequestId();
    }
}
