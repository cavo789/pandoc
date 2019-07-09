<?php

declare(strict_types = 1);

namespace Exceptions;

use Exceptions\PandocException;
use Throwable;

class PandocUnsupportedType extends PandocException
{
    /**
     * Constructor.
     *
     * @param string     $type
     * @param int        $code
     * @param \Throwable $previous
     */
    public function __construct(
        string $type,
        int $code = 501,
        Throwable $previous = null
    ) {
        $message = 'Unsupported type: ' . $type;
        parent::__construct($message, $code, $previous);
    }
}
