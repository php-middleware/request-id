<?php

namespace PhpMiddleware\RequestId\Generator;

use Ramsey\Uuid\UuidFactoryInterface;

final class RamseyUuid4Generator implements GeneratorInterface
{
    private $factory;

    public function __construct(UuidFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function generateRequestId(): string
    {
        return $this->factory->uuid4()->toString();
    }
}
