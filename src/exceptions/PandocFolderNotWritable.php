<?php

declare(strict_types = 1);

namespace Exceptions;

use Exceptions\PandocException;
use Throwable;

class PandocFolderNotWritable extends PandocException
{
    /**
     * Constructor.
     *
     * @param string     $foldername
     * @param int        $code
     * @param \Throwable $previous
     */
    public function __construct(
        string $foldername,
        int $code = 501,
        Throwable $previous = null
    ) {
        $message = 'Unable to write to the directory ' .
            $foldername;
        parent::__construct($message, $code, $previous);
    }
}
