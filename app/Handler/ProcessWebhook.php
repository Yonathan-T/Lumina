<?php

namespace App\Handler;

use App\Mail\SubscriptionConfirmed;
use App\Models\Payment\PolarCustomer;
use App\Models\Payment\PolarOrder;
use App\Models\Payment\PolarSubscription;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Illuminate\Support\Carbon;

/**
 * Handles incoming Polar webhook events.
 * All logic here must be idempotent and transactional.
 */
class ProcessWebhook extends ProcessWebhookJob
{
    /**
     * The main entry point for processing the webhook.
     */
    public function handle()
    {
        $payload = $this->webhookCall->payload;
        $eventType = $payload['type'];
        $data = $payload['data'] ?? [];

        Log::info("Processing Polar Webhook Event: {$eventType}", ['webhook_id' => $this->webhookCall->id]);

        DB::transaction(function () use ($eventType, $data) {
            try {
                if (empty($data)) {
                    Log::error("Webhook data payload is empty for event: {$eventType}");
                    return;
                }

                switch ($eventType) {
                    case "customer.created":
                    case "customer.updated":
                        $this->handleCustomerEvent($data);
                        break;

                    case "order.created":
                    case "order.paid":
                        $this->handleOrderPaidEvent($data);
                        break;
                    case "order.updated":
                        $this->handleOrderUpdatedEvent($data);
                        break;
                    case "order.refunded":
                    case "order.canceled":
                        $this->handleOrderRefundedOrCanceledEvent($data);
                        break;

                    case "subscription.created":
                    case "subscription.updated":
                    case "subscription.active":
                    case "subscription.paused":
                        $this->handleSubscriptionUpdateEvent($data);
                        break;

                    case "subscription.canceled":
                    case "subscription.revoked":
                        $this->handleSubscriptionRevokeEvent($data);
                        break;

                    default:
                        Log::info("Unhandled Polar event type: {$eventType}");
                        break;
                }

            } catch (Exception $e) {
                Log::error("Webhook processing failed for event {$eventType}: " . $e->getMessage(), [
                    'exception' => $e,
                    'payload' => $data,
                ]);
                throw $e;
            }
        });

    }

    /**
     * Handles customer creation and update events.
     * CRITICAL: Establishes the link between Polar Customer ID and local User.
     * @param array $data The 'data' object from the webhook payload.
     */
    protected function handleCustomerEvent(array $data)
    {
        $polarId = $data['id'];
        $email = $data['email'] ?? null;
        $name = $data['name'] ?? null;

        $billable = $this->findUserFromEmail($email);

        if (!$billable) {
            Log::error('Customer event for non-existent user. User must be registered before payment.', [
                'polar_id' => $polarId,
                'email' => $email
            ]);
            return;
        }

        $updateFields = [
            'trial_ends_at' => null,
            'billable_id' => $billable->getKey(),
            'billable_type' => $billable->getMorphClass(),
        ];

        try {
            $existingForUser = PolarCustomer::where('billable_type', $billable->getMorphClass())
                ->where('billable_id', $billable->getKey())
                ->first();

            if ($existingForUser && empty($existingForUser->polar_id)) {
                $existingForUser->fill($updateFields);
                $existingForUser->polar_id = $polarId;
                $existingForUser->save();
                $polarCustomer = $existingForUser;
            } else {
                $polarCustomer = PolarCustomer::updateOrCreate(
                    ['polar_id' => $polarId],
                    $updateFields
                );
            }

            Log::info('PolarCustomer record updated/created.', [
                'polar_id' => $polarId,
                'local_user_id' => $billable->id,
            ]);

            if ($billable->polar_customer_id !== $polarId) {
                $billable->polar_customer_id = $polarId;
                $billable->save();
            }
        } catch (Exception $e) {
            Log::error('PolarCustomer DB Insertion Failed.', [
                'error' => $e->getMessage(),
                'polar_id' => $polarId,
                'email_used' => $email
            ]);
        }
    }

    /**
     * Handles successful payment event (order.paid).
     * @param array $data The 'data' object from the webhook payload.
     */
    protected function handleOrderPaidEvent(array $data)
    {
        Log::info('Handling order event', [
            'id' => $data['id'] ?? null,
            'type' => $data['status'] ?? 'unknown',
            'customer_id' => $data['customer_id'] ?? null,
        ]);
        $polarOrderId = $data['id'] ?? null;
        $polarCustomerId = $data['customer_id'] ?? null;
        $customerEmail = $data['customer']['email'] ?? null;
        $customerName = $data['customer']['name'] ?? null;

        if (!$polarOrderId || !$polarCustomerId) {
            Log::error('Order event missing required fields.', [
                'id' => $data['id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'has_customer_email' => (bool) $customerEmail,
            ]);
            return;
        }

        $polarCustomer = PolarCustomer::where('polar_id', $polarCustomerId)->first();
        $user = null;

        if ($polarCustomer && $polarCustomer->billable) {
            $user = $polarCustomer->billable;
            Log::info('Found local User via PolarCustomer link.', ['User name' => $user->name]);
        } else {
            if ($customerEmail) {
                $user = $this->findUserFromEmail($customerEmail);
            }

            if ($user) {
                $existingForUser = PolarCustomer::where('billable_type', $user->getMorphClass())
                    ->where('billable_id', $user->getKey())
                    ->first();
                if ($existingForUser && empty($existingForUser->polar_id)) {
                    $existingForUser->polar_id = $polarCustomerId;
                    $existingForUser->save();
                    $polarCustomer = $existingForUser;
                } else {
                    $polarCustomer = PolarCustomer::updateOrCreate(
                        ['polar_id' => $polarCustomerId],
                        [
                            'billable_type' => $user->getMorphClass(),
                            'billable_id' => $user->getKey(),
                        ]
                    );
                }
            }
        }

        if (!$user) {
            Log::error('Order paid for unknown or unlinked user.', [
                'polar_order_id' => $polarOrderId,
                'polar_customer_id' => $polarCustomerId,
                'email' => $customerEmail
            ]);
            return;
        }

        try {
            if ($customerName && empty($user->name)) {
                $user->name = $customerName;
                $user->save();
            }

            $order = PolarOrder::updateOrCreate(
                ['polar_id' => $polarOrderId],
                [
                    'billable_type' => $user->getMorphClass(),
                    'billable_id' => $user->getKey(),
                    'customer_id' => $polarCustomerId,
                    'status' => $data['status'] ?? 'paid',
                    'amount' => (int) ($data['total_amount'] ?? 0),
                    'tax_amount' => (int) ($data['tax_amount'] ?? 0),
                    'refunded_amount' => (int) ($data['refunded_amount'] ?? 0),
                    'refunded_tax_amount' => (int) ($data['refunded_tax_amount'] ?? 0),
                    'currency' => $data['currency'] ?? 'USD',
                    'billing_reason' => $data['billing_reason'] ?? 'purchase',
                    'product_id' => $data['product_id'] ?? ($data['product']['id'] ?? 'unknown'),
                    'refunded_at' => isset($data['refunded_at']) && $data['refunded_at'] ? Carbon::parse($data['refunded_at']) : null,
                    'ordered_at' => isset($data['created_at']) && $data['created_at'] ? Carbon::parse($data['created_at']) : now(),
                ]
            );
            Log::info('PolarOrder record created/updated.', ['polar_id' => $polarOrderId, 'local_user_id' => $user->id]);

            if ($user->polar_customer_id !== $polarCustomerId) {
                $user->polar_customer_id = $polarCustomerId;
            }
            if (!isset($user->is_subscribed) || $user->is_subscribed !== true) {
                $user->is_subscribed = true;
            }
            $user->save();
            Log::info('User subscription status and customer ID updated.', ['user_id' => $user->id]);

            try {
                DB::afterCommit(function () use ($user, $order) {
                    Mail::to($user->email)->queue(new SubscriptionConfirmed($user, $order));
                });
                Log::info('SubscriptionConfirmed email queued after commit.', ['email' => $user->email]);
            } catch (Exception $mailEx) {
                Log::error('Failed to queue SubscriptionConfirmed email.', [
                    'error' => $mailEx->getMessage(),
                    'email' => $user->email,
                ]);
            }

        } catch (Exception $e) {
            Log::error('PolarOrder processing failed.', ['error' => $e->getMessage(), 'polar_id' => $polarOrderId, 'data_keys' => array_keys($data)]);
            throw $e;
        }
    }

    /**
     * Handles order.updated event - idempotently updates mutable fields.
     */
    protected function handleOrderUpdatedEvent(array $data)
    {
        $polarOrderId = $data['id'] ?? null;
        if (!$polarOrderId) {
            Log::error('Order update missing id.', ['keys' => array_keys($data)]);
            return;
        }
        try {
            PolarOrder::where('polar_id', $polarOrderId)->update([
                'status' => $data['status'] ?? 'updated',
                'amount' => (int) ($data['total_amount'] ?? 0),
                'tax_amount' => (int) ($data['tax_amount'] ?? 0),
                'refunded_amount' => (int) ($data['refunded_amount'] ?? 0),
                'refunded_tax_amount' => (int) ($data['refunded_tax_amount'] ?? 0),
                'currency' => $data['currency'] ?? 'usd',
                'billing_reason' => $data['billing_reason'] ?? 'purchase',
                'product_id' => $data['product_id'] ?? ($data['product']['id'] ?? 'unknown'),
                'refunded_at' => isset($data['refunded_at']) && $data['refunded_at'] ? Carbon::parse($data['refunded_at']) : null,
                'ordered_at' => isset($data['created_at']) && $data['created_at'] ? Carbon::parse($data['created_at']) : now(),
            ]);
            Log::info('PolarOrder updated from order.updated event.', ['polar_id' => $polarOrderId]);
        } catch (Exception $e) {
            Log::error('PolarOrder update failed.', ['error' => $e->getMessage(), 'polar_id' => $polarOrderId]);
            throw $e;
        }
    }

    /**
     * Handles order.refunded and order.canceled events.
     */
    protected function handleOrderRefundedOrCanceledEvent(array $data)
    {
        $polarOrderId = $data['id'] ?? null;
        if (!$polarOrderId) {
            Log::error('Order refund/cancel missing id.', ['keys' => array_keys($data)]);
            return;
        }
        $status = $data['status'] ?? ($data['type'] ?? 'refunded');
        try {
            PolarOrder::where('polar_id', $polarOrderId)->update([
                'status' => $status,
                'refunded_amount' => (int) ($data['refunded_amount'] ?? 0),
                'refunded_tax_amount' => (int) ($data['refunded_tax_amount'] ?? 0),
                'refunded_at' => isset($data['refunded_at']) && $data['refunded_at'] ? Carbon::parse($data['refunded_at']) : now(),
            ]);
            Log::info('PolarOrder refunded/canceled updated.', ['polar_id' => $polarOrderId, 'status' => $status]);
        } catch (Exception $e) {
            Log::error('PolarOrder refund/cancel update failed.', ['error' => $e->getMessage(), 'polar_id' => $polarOrderId]);
            throw $e;
        }
    }


    /**
     * Handles subscription status changes (active, created, updated, paused).
     * CRITICAL FIX: Correctly links subscription to local User via the PolarCustomer record.
     * @param array $data The 'data' object from the webhook payload.
     */
    protected function handleSubscriptionUpdateEvent(array $data)
    {
        $status = $data['status'];
        $polarCustomerId = $data['customer_id'];
        $customerEmail = $data['customer']['email'] ?? null;
        $customerName = $data['customer']['name'] ?? null;

        $polarCustomer = PolarCustomer::where('polar_id', $polarCustomerId)->first();

        if (!$polarCustomer || !$polarCustomer->billable) {
            if ($customerEmail) {
                $user = $this->findUserFromEmail($customerEmail);
                if ($user) {
                    $polarCustomer = PolarCustomer::updateOrCreate(
                        ['polar_id' => $polarCustomerId],
                        [
                            'billable_type' => $user->getMorphClass(),
                            'billable_id' => $user->getKey(),
                        ]
                    );
                } else {
                    Log::error('Subscription update failed: User not found. User must be registered before payment.', [
                        'subscription_id' => $data['id'],
                        'customer_id' => $polarCustomerId,
                        'email' => $customerEmail,
                    ]);
                    return;
                }
            } else {
                Log::error('Subscription update failed: Cannot resolve user without email.', [
                    'subscription_id' => $data['id'],
                    'customer_id' => $polarCustomerId,
                ]);
                return;
            }
        }

        $user = $polarCustomer->billable;

        PolarSubscription::updateOrCreate(
            ['polar_id' => $data['id']],
            [
                'billable_type' => $user->getMorphClass(),
                'billable_id' => $user->getKey(),

                'type' => $data['recurring_interval'] ? 'recurring' : 'one_time',
                'status' => $status,
                'product_id' => $data['product_id'],
                'current_period_end' => $data['current_period_end'] ? Carbon::parse($data['current_period_end']) : null,
                'trial_ends_at' => $data['trial_end'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]
        );
        Log::info('PolarSubscription record updated/created.', ['id' => $data['id'], 'status' => $status]);

        if ($status === 'active') {
            $user->is_subscribed = true;
            $user->save();
            Log::info('User access confirmed active via subscription event.', ['user_id' => $user->id]);
        }
    }

    /**
     * Handles subscription revocation or cancellation events.
     * @param array $data The 'data' object from the webhook payload.
     */
    protected function handleSubscriptionRevokeEvent(array $data)
    {
        $status = $data['status'];
        $subscriptionId = $data['id'];
        $polarCustomerId = $data['customer_id'];

        PolarSubscription::where('polar_id', $subscriptionId)->update(['status' => $status]);
        Log::warning('PolarSubscription status updated to revoked/canceled.', ['id' => $subscriptionId, 'status' => $status]);

        $polarCustomer = PolarCustomer::where('polar_id', $polarCustomerId)->first();

        if ($polarCustomer && $polarCustomer->billable) {
            $user = $polarCustomer->billable;

            $hasOtherActiveSubs = $user->subscriptions()
                ->where('status', 'active')
                ->where('polar_id', '!=', $subscriptionId)
                ->exists();

            if (!$hasOtherActiveSubs) {
                $user->is_subscribed = false;
                $user->save();
                Log::info('User access revoked/canceled (no other active subscriptions found).', ['user_id' => $user->id]);
            } else {
                Log::info('Subscription canceled, but user retains access due to other active subscriptions.', ['user_id' => $user->id]);
            }
        }
    }

    /**
     * Case-insensitive lookup for User by email.
     * Returns null if user doesn't exist (user must be registered before payment).
     */
    private function findUserFromEmail(?string $email): ?User
    {
        if (!$email) {
            Log::error('Email is required to find a User.');
            return null;
        }

        $normalizedEmail = strtolower(trim($email));

        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if ($user) {
            Log::info('Found existing user from webhook email.', [
                'user_id' => $user->id,
                'email' => $normalizedEmail,
            ]);
        } else {
            Log::warning('User not found for webhook email. User must register before payment.', [
                'email' => $normalizedEmail,
            ]);
        }

        return $user;
    }

}
