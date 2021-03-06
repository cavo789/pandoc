<?php

declare(strict_types = 1);

namespace Exceptions;

use Exceptions\PandocException;
use Throwable;

class PandocOutputTypeNotSupported extends PandocException
{
    /*
     * Constructor.
     *
     * @param string     $message
     * @param int        $code
     * @param Throwable $previous
     */
    public function __construct(
        string $message = 'Please use a valid type when calling the ' .
        'setOutputType() method.',
        int $code = 501,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
