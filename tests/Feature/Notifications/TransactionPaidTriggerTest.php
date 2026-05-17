<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\NotificationType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\MayarService;
use App\Services\PaymentActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TransactionPaidTriggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_transaction_publishes_notification_to_user(): void
    {
        Mail::fake();
        $this->mock(MayarService::class, function ($mock) {
            $mock->shouldReceive('getInvoice')->andReturn(['status' => 'paid', 'transactionStatus' => 'paid']);
        });

        $plan = Plan::factory()->premium()->create();
        $user = User::factory()->create();
        $txn = Transaction::create([
            'user_id'            => $user->id,
            'plan_id'            => $plan->id,
            'invoice_number'     => 'INV-TRIG-1',
            'amount'             => 99000,
            'payment_method'     => PaymentMethod::Mayar,
            'status'             => PaymentStatus::Pending,
            'payment_gateway_id' => 'gw-trig-1',
        ]);

        $service = app(PaymentActivationService::class);
        $service->verifyAndActivate($txn);

        $notif = UserNotification::where('user_id', $user->id)
            ->where('type', NotificationType::TransactionPaid->value)
            ->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString($plan->name, $notif->title);
    }
}
