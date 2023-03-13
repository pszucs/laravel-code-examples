<?php

namespace App\Services\MergeUsers\Pipes;

use App\Services\MergeUsers\DataTransferObjects\MergeUsersPayload;
use Closure;

class DeleteDuplicateUserModel implements MergeUsersPipe
{
    public function handle(MergeUsersPayload $payload, Closure $next)
    {
        $payload->removeUser()->delete();

        return $next($payload);
    }
}
