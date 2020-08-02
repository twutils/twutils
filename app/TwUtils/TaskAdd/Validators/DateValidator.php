<?php

namespace App\TwUtils\TaskAdd\Validators;

use App\Exceptions\TaskAddException;
use Symfony\Component\HttpFoundation\Response;

class DateValidator
{
    public function apply($requestData)
    {
        $settings = $requestData['settings'] ?? null;
        // TODO: Potential bug on PHP 7.4 if $settings is null
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