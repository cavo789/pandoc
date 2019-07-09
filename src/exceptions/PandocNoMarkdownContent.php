<?php

declare(strict_types = 1);

namespace Exceptions;

use Exceptions\PandocException;
use Throwable;

class PandocNoMarkdownContent extends PandocException
{
    /*
     * Constructor.
     *
     * @param string     $message
     * @param int        $code
     * @param Throwable $previous
     */
    public function __construct(
        string $message = 'You\'ve called ' .
        'the export feature without giving any content to ' .
        'export. Please give an non-empty string to the ' .
        'setMarkdown() method.',
        int $code = 501,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
