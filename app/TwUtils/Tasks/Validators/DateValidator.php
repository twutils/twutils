<?php

namespace App\TwUtils\Tasks\Validators;

use App\Exceptions\TaskAddException;
use Symfony\Component\HttpFoundation\Response;

class DateValidator
{
    public function apply($requestData, $user)
    {
        $settings = $requestData['settings'] ?? null;

        $startDate = $settings['start_date'] ?? null;
        $endDate = $settings['end_date'] ?? null;

        $shouldValidate = $endDate !== null || $startDate !== null;

        $datesErrors = validator()->make(
            ['start_date' => $startDate, 'end_date' => $endDate],
            [
                'start_date' => 'nullable|date|date_format:Y-m-d'.(is_null($endDate) ? '' : '|before:end_date'),
                'end_date'   => 'nullable|date|date_format:Y-m-d'.(is_null($startDate) ? '' : '|after:start_date'),
            ]
        )->errors()->all();

        if ($shouldValidate && ! empty($datesErrors)) {
            throw new TaskAddException($datesErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
