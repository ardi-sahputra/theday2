<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'plan_id'           => ['required', 'exists:plans,id'],
            'delivery_mode'     => ['required', 'in:link,email'],
            'recipient_email'   => ['required_if:delivery_mode,email', 'nullable', 'email', 'max:255'],
            'message'           => ['nullable', 'string', 'max:280'],
            'duration_days'     => ['nullable', 'integer', 'min:1', 'max:3650'],
            'custom_expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }
}
