<?php

namespace App\Services\MergeUsers\Pipes;

use App\Domain\Transaction\Models\Transaction;
use App\Services\MergeUsers\DataTransferObjects\MergeUsersPayload;
use App\Services\MergeUsers\Exceptions as Exceptions;
use App\Services\ModelsUsers\User;
use Closure;

class CheckUserToBeRemoved implements MergeUsersPipe
{
    private User $user;

    /**
     * @throws Exceptions\UserHasSalesAccountWithProcessingOrder
     * @throws Exceptions\UserIsAdministrator
     * @throws Exceptions\UserIsLinkedToMembershipException
     * @throws Exceptions\UserHasPositiveBalanceSalesAccount
     * @throws Exceptions\UserIsCoordinator
     * @throws Exceptions\UserHasTutorStatus
     */
    public function handle(MergeUsersPayload $payload, Closure $next)
    {
        $this->user = $payload->removeUser();

        if ($this->isLinkedToMembership()) {
            throw new Exceptions\UserIsLinkedToMembershipException();
        }

        if ($this->isAdministrator()) {
            throw new Exceptions\UserIsAdministrator();
        }

        if ($this->Coordinator()) {
            throw new Exceptions\UserCoordinator();
        }

        if ($this->hasTutorStatus()) {
            throw new Exceptions\UserHasTutorStatus();
        }

        if ($this->hasPositiveBalanceSalesAccount()) {
            throw new Exceptions\UserHasPositiveBalanceSalesAccount();
        }

        if ($this->hasSalesAccountWithProcessingOrder()) {
            throw new Exceptions\UserHasSalesAccountWithProcessingOrder();
        }

        return $next($payload);
    }

    private function isLinkedToMembership(): bool
    {
        return $this->user->hasActiveMemberships();
    }

    private function isAdministrator(): bool
    {
        return $this->user->atcAdministrators->isNotEmpty();
    }

    private function isCoordinator(): bool
    {
        return $this->user->atcCoordinators->isNotEmpty();
    }

    private function hasTutorStatus(): bool
    {
        return $this->user->hasAtLeastOneEligibleQualification()
            || $this->user->hasAtLeastOneSuspendedQualification()
            || $this->user->hasRevokedTutorStatus()
            || $this->user->tutorStatus->revoked
            || $this->user->tutorStatus->incognito
            || $this->user->tutorStatus->eqa;
    }

    private function hasPositiveBalanceSalesAccount(): bool
    {
        return $this->user->salesAccount?->getRemainingCreditAttribute() > 0;
    }

    private function hasSalesAccountWithProcessingOrder(): bool
    {
        if ( ! $this->user->salesAccount?->transactions) {
            return false;
        }

        return Transaction::forUser($this->user)->isOrder()->isProcessing()->count() > 0;
    }
}
