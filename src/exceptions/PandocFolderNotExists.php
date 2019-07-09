<?php

declare(strict_types = 1);

namespace Exceptions;

use Exceptions\PandocException;
use Throwable;

class PandocFolderNotExists extends PandocException
{
    /*
     * Constructor.
     *
     * @param string    $message
     * @param int       $code
     * @param Throwable $previous
     */
    public function __construct(
        string $message = ' Please give an existing folder name to the ' .
        'setOutputFolder() method.',
        int $code = 501,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
