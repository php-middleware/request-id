<?php

namespace PhpMiddleware\RequestId\Generator;

interface GeneratorInterface
{
    public function generateRequestId(): string;
}
