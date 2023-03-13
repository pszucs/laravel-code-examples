<?php

namespace App\Services\DirectDebit\Gateway;

use App\Domain\DirectDebit\Actions\CancelDirectDebitSubscriptionAction;
use App\Domain\DirectDebit\DataTransferObjects\SubscriptionCancellation;
use App\Domain\DirectDebit\Models\DirectDebitSubscription;
use App\Domain\Membership\Models\MembershipPackage;
use App\Domain\User\Models\User;
use GoCardlessPro\Client;
use GoCardlessPro\Resources\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class SubscriptionFeature
{
    public function __construct(private readonly Client $client)
    {
    }
    
    public function processSubscriptionEvent(Event $event): void
    {
        if (in_array($event->action, ['cancelled', 'expired', 'customer_approval_denied', 'finished'])) {
            try {
                $gatewaySubscriptionId = $event->links->subscription;

                $this->cancelSubscription($event, $gatewaySubscriptionId);
            } catch (ModelNotFoundException $exception) {
                Log::error('Subscription cancelled that was not found', [
                    'event' => $event->id,
                    'mandate' => $gatewaySubscriptionId,
                ]);
            }
        }
    }
    
    public function getSubscription(User $user, MembershipPackage $membershipPackage)
    {
        $subscriptionGatewayId = $user->membership($membershipPackage)
            ->details
            ->subscriptions
            ->last()
            ->gateway_subscription_id;
    
        return $this->client->subscriptions()->get($subscriptionGatewayId);
    }

    protected function cancelSubscription(Event $event, string $subscriptionId): void
    {
        $subscription = DirectDebitSubscription::where('gateway_subscription_id', $subscriptionId)->firstOrFail();

        $cancellationDto = new SubscriptionCancellation(
            $subscription,
            $event->details->description ?? null,
            false
        );

        /** @var CancelDirectDebitSubscriptionAction $action */
        $action = app(CancelDirectDebitSubscriptionAction::class);
        $action->handle($cancellationDto);
    }
}
