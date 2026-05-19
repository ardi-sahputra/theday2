<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        static $seq = 0;
        $seq++;
        $today = now()->format('Ymd');

        return [
            'user_id'        => User::factory(),
            'plan_id'        => null,
            'invoice_number' => 'INV-' . $today . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
            'amount'         => 35000,
            'payment_method' => PaymentMethod::Mayar,
            'status'         => PaymentStatus::Pending,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status'  => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => PaymentStatus::Pending]);
    }
}
