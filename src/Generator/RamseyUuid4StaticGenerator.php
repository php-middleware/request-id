<?php

namespace PhpMiddleware\RequestId\Generator;

use Ramsey\Uuid\Uuid;

final class RamseyUuid4StaticGenerator implements GeneratorInterface
{
    public function generateRequestId(): string
    {
        return Uuid::uuid4()->toString();
    }
}
