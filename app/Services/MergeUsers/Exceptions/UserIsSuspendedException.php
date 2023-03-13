<?php

namespace App\Domain\User\Merge\Exceptions;

class UserIsSuspendedException extends \Exception
{
    protected $message = 'Suspended users cannot be merged.';
}
