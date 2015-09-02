<?php

namespace PhpMiddleware\RequestId\Generator;

interface GeneratorInterface
{
    /**
     * @return mixed unique value
     */
    public function generateRequestId();
}
