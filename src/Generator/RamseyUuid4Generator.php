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

    /**
     * @return string
     */
    public function generateRequestId()
    {
        return $this->factory->uuid4()->toString();
    }
}
