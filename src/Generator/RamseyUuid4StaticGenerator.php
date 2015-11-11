<?php

namespace PhpMiddleware\RequestId\Generator;

use Ramsey\Uuid\Uuid;

final class RamseyUuid4StaticGenerator implements GeneratorInterface
{
    /**
     * @return string
     */
    public function generateRequestId()
    {
        return Uuid::uuid4()->toString();
    }
}
