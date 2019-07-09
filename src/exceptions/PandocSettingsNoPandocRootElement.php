<?php

declare(strict_types = 1);

namespace Exceptions;

use Exceptions\PandocException;
use Throwable;

class PandocSettingsNoPandocRootElement extends PandocException
{
    /*
     * Constructor.
     *
     * @param string     $message
     * @param int        $code
     * @param  $previous
     */
    public function __construct(
        string $message = 'All settings should be placed in a ' .
            'root element called "pandoc".',
        int $code = 501,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
