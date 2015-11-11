<?php

namespace PhpMiddleware\RequestId\Generator;

interface GeneratorInterface
{
    /**
     * @return string unique value
     */
    public function generateRequestId();
}
