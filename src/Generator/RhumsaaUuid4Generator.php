<?php

namespace PhpMiddleware\RequestId\Generator;

use Rhumsaa\Uuid\Uuid;

final class RhumsaaUuid4Generator implements GeneratorInterface
{
    /**
     * @return string
     */
    public function generateRequestId()
    {
        return (string) Uuid::uuid4();
    }
}
