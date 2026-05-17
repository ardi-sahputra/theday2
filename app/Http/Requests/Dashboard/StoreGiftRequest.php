<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\Plan;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'plan_id' => [
                'required',
                'exists:plans,id',
                function (string $attribute, mixed $value, Closure $fail) {
                    $plan = Plan::find($value);
                    if (! $plan || $plan->slug !== 'premium') {
                        $fail('Plan tidak valid untuk gift.');
                    }
                },
            ],
            'delivery_mode'   => ['required', 'in:link,email'],
            'recipient_email' => ['required_if:delivery_mode,email', 'nullable', 'email', 'max:255'],
            'message'         => ['nullable', 'string', 'max:280'],
        ];
    }
}
