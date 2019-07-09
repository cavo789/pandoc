<?php

declare(strict_types = 1);

namespace Exceptions;

use Exceptions\PandocException;
use Throwable;

class PandocTemplateNotFound extends PandocException
{
    /*
     * Constructor.
     *
     * @param string     $filename
     * @param int        $code
     * @param  $previous
     */
    public function __construct(
        string $filename,
        int $code = 501,
        Throwable $previous = null
    ) {
        $message = 'The template ' . $filename . ' is not found';
        parent::__construct($message, $code, $previous);
    }
}
