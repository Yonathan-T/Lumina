<?php

namespace App\Mail;

use App\Models\Payment\PolarOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance who made the purchase.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The confirmed Polar order record.
     *
     * @var \App\Models\Payment\PolarOrder
     */
    public $order;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Payment\PolarOrder $order
     * @return void
     */
    public function __construct(User $user, PolarOrder $order)
    {
        $this->user = $user;
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Amount and currency
        $amountCents = (int) ($this->order->amount ?? 0);
        $displayAmount = number_format($amountCents / 100, 2) . ' ' . strtoupper((string) $this->order->currency);

        // Product/plan metadata from config map (fallbacks applied)
        $productId = (string) ($this->order->product_id ?? '');
        $productConfig = config('polar_products.products.' . $productId, []);
        $productName = $productConfig['name'] ?? 'Premium Plan';
        $benefits = $productConfig['benefits'] ?? [
            'AI-powered journaling insights',
            'Advanced analytics and trends',
            'Priority support',
            'Enhanced mobile experience',
        ];

        // Order identifiers
        $orderId = (string) ($this->order->polar_id ?? '');

        return $this->markdown('emails.billing.subscription-confirmed')
            ->subject('Your ' . $productName . ' Purchase Confirmed!')
            ->with([
                'displayAmount' => $displayAmount,
                'productName' => $productName,
                'orderId' => $orderId,
                'benefits' => $benefits,
                'userName' => (string) ($this->user->name ?? ''),
            ]);
    }
}
