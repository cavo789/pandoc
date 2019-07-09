<?php

declare(strict_types = 1);

namespace Exceptions;

use Throwable;

abstract class PandocException extends \Exception
{
    /**
     * Undocumented variable.
     *
     * @var Throwable
     */
    private $previous = null;

    /*
     * Constructor
     *
     * @param string $message
     * @param integer $code
     * @param Throwable $previous
     */
    public function __construct(
        string $message,
        int $code = 501,
        Throwable $previous = null
    ) {
        $this->previous = $previous;
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        if (null !== $this->previous) {
            echo $this->previous->getMessage();
        }

        $arr            =[];
        $arr['status']  = 'error';
        $arr['message'] = get_class($this) . '<br/><br/>' . $this->message;

        die(json_encode($arr));
    }
}
