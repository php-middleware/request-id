<?php

namespace PhpMiddleware\RequestId\Exception;

use UnexpectedValueException;

class InvalidRequestId extends UnexpectedValueException implements RequestIdExceptionInterface
{
}
