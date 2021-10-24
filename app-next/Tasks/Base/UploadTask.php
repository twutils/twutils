<?php

namespace AppNext\Tasks\Base;

abstract class UploadTask extends Task
{
    protected string $scope = 'write';
}
