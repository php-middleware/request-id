<?php

namespace PhpMiddleware\RequestId\Generator;

use Rhumsaa\Uuid\Uuid;

final class RhumsaaUuid1Generator implements GeneratorInterface
{
    /**
     * @var int|string
     */
    private $node;

    /**
     * @var int
     */
    private $clockSeq;

    /**
     * Generate a version 1 UUID from a host ID, sequence number, and the current time.
     * If $node is not given, we will attempt to obtain the local hardware
     * address. If $clockSeq is given, it is used as the sequence number;
     * otherwise a random 14-bit sequence number is chosen.
     *
     * @param int|string $node A 48-bit number representing the hardware
     *                         address. This number may be represented as
     *                         an integer or a hexadecimal string.
     * @param int $clockSeq A 14-bit number used to help avoid duplicates that
     *                      could arise when the clock is set backwards in time
     *                      or if the node ID changes.
     */
    public function __construct($node = null, $clockSeq = null)
    {
        $this->node = $node;
        $this->clockSeq = $clockSeq;
    }

    /**
     * @return mixed
     */
    public function generateRequestId()
    {
        return (string) Uuid::uuid1($this->node, $this->clockSeq);
    }
}
