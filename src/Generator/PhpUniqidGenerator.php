<?php

namespace PhpMiddleware\RequestId\Generator;

final class PhpUniqidGenerator implements GeneratorInterface
{
    protected $prefix;
    protected $moreEntropy;

    /**
     * @link http://php.net/manual/en/function.uniqid.php
     */
    public function __construct(string $prefix = '', bool $moreEntropy = false)
    {
        $this->prefix = $prefix;
        $this->moreEntropy = $moreEntropy;
    }

    public function generateRequestId(): string
    {
        return uniqid($this->prefix, $this->moreEntropy);
    }
}
