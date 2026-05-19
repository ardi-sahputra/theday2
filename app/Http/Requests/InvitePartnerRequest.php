<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\CoupleLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvitePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
    }

    public function rules(): array
    {
        $authId = $this->user()->id;

        return [
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::notIn([strtolower((string) $this->user()->email)]),
                function (string $attribute, mixed $value, \Closure $fail) use ($authId) {
                    // Owner cannot already have any active or pending link
                    $existing = CoupleLink::where('owner_id', $authId)
                        ->whereIn('status', [CoupleLink::STATUS_PENDING, CoupleLink::STATUS_ACTIVE])
                        ->exists();
                    if ($existing) {
                        $fail('Kamu sudah punya undangan partner aktif atau menunggu.');
                        return;
                    }

                    // Invitee, if registered, must not already be partner elsewhere
                    $invitee = \App\Models\User::whereRaw('LOWER(email) = ?', [$value])->first();
                    if ($invitee !== null) {
                        $linkedElsewhere = CoupleLink::where('partner_id', $invitee->id)
                            ->where('status', CoupleLink::STATUS_ACTIVE)
                            ->exists();
                        if ($linkedElsewhere) {
                            $fail('Email ini sudah terhubung ke akun lain.');
                        }
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.not_in' => 'Kamu tidak bisa mengundang diri sendiri.',
        ];
    }
}
