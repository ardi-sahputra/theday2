<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Rules\InternalOrSameHostUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationBroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'body'              => ['nullable', 'string', 'max:500'],
            'action_url'        => ['nullable', 'string', 'max:255', new InternalOrSameHostUrl()],
            'category'          => ['required', Rule::in(['guest', 'payment', 'gift', 'reminder', 'onboarding', 'engagement', 'system'])],
            'target_type'       => ['required', Rule::in(['all', 'users'])],
            'target_user_ids'   => ['required_if:target_type,users', 'array'],
            'target_user_ids.*' => ['string', 'exists:users,id'],
            'send_mode'         => ['required', Rule::in(['immediate', 'scheduled'])],
            'scheduled_at'      => ['required_if:send_mode,scheduled', 'nullable', 'date', 'after:now'],
        ];
    }
}
