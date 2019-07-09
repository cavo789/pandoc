<?php

declare(strict_types = 1);

namespace Exceptions;

use Exceptions\PandocException;
use Throwable;

class PandocFileProtected extends PandocException
{
    /*
     * Constructor.
     *
     * @param string     $filename
     * @param int        $code
     * @param Throwable $previous
     */
    public function __construct(
        string $filename,
        int $code = 501,
        Throwable $previous = null
    ) {
        $message = 'File ' . $filename . ' can\'t be removed from the filesystem';
        parent::__construct($message, $code, $previous);
    }
}
