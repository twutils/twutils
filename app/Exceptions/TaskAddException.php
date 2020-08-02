<?php

namespace App\Exceptions;

use Exception;

class TaskAddException extends Exception
{
    protected $response;

    public function __construct(array $errors, int $statusCode, array $data = [])
    {
        parent::__construct('Invalid Request');

        $this->response = response()->json([
            'ok' => false,
            'errors' => $errors,
            'data' => $data,
        ], $statusCode);
    }

    public function toResponse()
    {
        return $this->response;
    }
}
