<?php

namespace PhpMiddleware\RequestId\Generator;

class PhpUniqidGenerator implements GeneratorInterface
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var bool
     */
    protected $moreEntropy;

    /**
     * @param string $prefix
     * @param bool $moreEntropy
     *
     * @link http://php.net/manual/en/function.uniqid.php
     */
    public function __construct($prefix = '', $moreEntropy = false)
    {
        $this->prefix = (string) $prefix;
        $this->moreEntropy = (bool) $moreEntropy;
    }

    /**
     * @return mixed
     */
    public function generateRequestId()
    {
        return uniqid($this->prefix, $this->moreEntropy);
    }
}
