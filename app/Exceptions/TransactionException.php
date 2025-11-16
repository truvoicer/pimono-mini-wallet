<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class TransactionException extends Exception
{
    public function __construct(
        $message = "Transaction error occurred.",
        $code = Response::HTTP_UNPROCESSABLE_ENTITY
    ) {
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        return back()->withErrors(['error' => $this->getMessage()]);
    }
}
