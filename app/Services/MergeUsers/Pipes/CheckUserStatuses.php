<?php

namespace App\Services\MergeUsers\Pipes;

use App\Enums\UserStatusEnum;
use App\Services\MergeUsers\DataTransferObjects\MergeUsersPayload;
use App\Services\MergeUsers\Exceptions\UserIsCancelledException;
use App\Services\MergeUsers\Exceptions\UserIsSuspendedException;
use Closure;

class CheckUserStatuses
{
    /**
     * @throws UserIsSuspendedException
     * @throws UserIsCancelledException
     */
    public function handle(MergeUsersPayload $payload, Closure $next)
    {
        if (
            $payload->keepUser()->status->equals(UserStatusEnum::suspended())
            || $payload->removeUser()->status->equals(UserStatusEnum::suspended())
        ) {
            throw new UserIsSuspendedException();
        }
        
        if (
            $payload->keepUser()->status->equals(UserStatusEnum::cancelled())
            || $payload->removeUser()->status->equals(UserStatusEnum::cancelled())
        ) {
            throw new UserIsCancelledException();
        }
        
        return $next($payload);
    }
}
