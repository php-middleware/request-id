<?php

namespace PhpMiddleware\RequestId\Generator;

use Ramsey\Uuid\UuidFactoryInterface;

final class RamseyUuid3Generator implements GeneratorInterface
{
    private $ns;

    private $name;

    private $factory;

    /**
     * @param UuidFactoryInterface $factory
     * @param string $ns
     * @param string $name
     */
    public function __construct(UuidFactoryInterface $factory, $ns, $name)
    {
        $this->factory = $factory;
        $this->ns = $ns;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function generateRequestId()
    {
        return $this->factory->uuid3($this->ns, $this->name)->toString();
    }
}
