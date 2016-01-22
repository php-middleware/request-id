<?php

namespace PhpMiddleware\RequestId\Exception;

use UnexpectedValueException;

class MissingRequestId extends UnexpectedValueException implements RequestIdExceptionInterface
{
}
