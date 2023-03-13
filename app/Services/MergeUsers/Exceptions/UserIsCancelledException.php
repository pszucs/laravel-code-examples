<?php

namespace App\Services\MergeUsers\Exceptions;

class UserIsCancelledException extends \Exception
{
    protected $message = 'Cancelled users cannot be merged.';
}
