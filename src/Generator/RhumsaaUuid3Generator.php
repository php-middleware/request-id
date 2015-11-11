<?php

namespace PhpMiddleware\RequestId\Generator;

use Rhumsaa\Uuid\Uuid;

final class RhumsaaUuid3Generator implements GeneratorInterface
{
    /**
     * @var Uuid|string
     */
    private $ns;

    /**
     * @var string
     */
    private $name;

    /**
     * Generate a version 3 UUID based on the MD5 hash of a namespace identifier (which
     * is a UUID) and a name (which is a string).
     *
     * @param Uuid|string $ns The UUID namespace in which to create the named UUID
     * @param string $name The name to create a UUID for
     */
    public function __construct($ns, $name)
    {
        $this->ns = $ns;
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function generateRequestId()
    {
        return (string) Uuid::uuid3($this->ns, $this->name);
    }
}
