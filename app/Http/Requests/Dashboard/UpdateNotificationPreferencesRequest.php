<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'guest_enabled'      => ['required', 'boolean'],
            'payment_enabled'    => ['required', 'boolean'],
            'gift_enabled'       => ['required', 'boolean'],
            'reminder_enabled'   => ['required', 'boolean'],
            'onboarding_enabled' => ['required', 'boolean'],
            'engagement_enabled' => ['required', 'boolean'],
            'system_enabled'     => ['required', 'boolean'],
        ];
    }
}
