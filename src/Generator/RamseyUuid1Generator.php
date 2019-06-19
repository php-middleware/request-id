<?php

namespace PhpMiddleware\RequestId\Generator;

use Ramsey\Uuid\UuidFactoryInterface;

final class RamseyUuid1Generator implements GeneratorInterface
{
    private $node;
    private $clockSeq;
    private $factory;

    /**
     * @param int|string $node
     */
    public function __construct(UuidFactoryInterface $factory, $node = null, int $clockSeq = null)
    {
        $this->factory = $factory;
        $this->node = $node;
        $this->clockSeq = $clockSeq;
    }

    public function generateRequestId(): string
    {
        return $this->factory->uuid1($this->node, $this->clockSeq)->toString();
    }
}
