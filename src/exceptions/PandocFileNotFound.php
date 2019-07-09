<?php

declare(strict_types = 1);

namespace Exceptions;

use Exceptions\PandocException;
use Throwable;

class PandocFileNotFound extends PandocException
{
    /**
     * Constructor.
     *
     * @param string     $filename
     * @param int        $code
     * @param \Throwable $previous
     */
    public function __construct(
        string $filename,
        int $code = 501,
        Throwable $previous = null
    ) {
        $message = 'File ' . $filename . ' not found';
        parent::__construct($message, $code, $previous);
    }
}
