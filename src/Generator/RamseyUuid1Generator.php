<?php

namespace PhpMiddleware\RequestId\Generator;

use Ramsey\Uuid\UuidFactoryInterface;

final class RamseyUuid1Generator implements GeneratorInterface
{
    private $node;

    private $clockSeq;

    private $factory;

    /**
     * @param UuidFactoryInterface $factory
     * @param int|string $node
     * @param int $clockSeq
     */
    public function __construct(UuidFactoryInterface $factory, $node = null, $clockSeq = null)
    {
        $this->factory = $factory;
        $this->node = $node;
        $this->clockSeq = $clockSeq;
    }

    /**
     * @return string
     */
    public function generateRequestId()
    {
        return $this->factory->uuid1($this->node, $this->clockSeq)->toString();
    }
}
