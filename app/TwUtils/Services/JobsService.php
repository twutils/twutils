<?php

namespace App\TwUtils\Services;

use Carbon\Carbon;

class JobsService
{
    public function getNextJobDelayFromHeaders(array $headers)
    {
        if (! isset($headers['x_rate_limit_remaining'])) {
            return;
        }

        return $headers['x_rate_limit_remaining'] == '1' || $headers['x_rate_limit_remaining'] == '0' || $headers['x_rate_limit_remaining'] == '01' ? Carbon::createFromTimestamp($headers['x_rate_limit_reset']) : null;
    }
}
