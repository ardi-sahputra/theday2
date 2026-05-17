<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use App\Rules\NoOverlappingDiscount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StorePlanDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user('admin') === null) {
            return false;
        }

        $plan = Plan::find($this->input('plan_id'));

        return $plan !== null && $plan->slug === 'premium';
    }

    public function rules(): array
    {
        return [
            'plan_id'   => ['required', 'exists:plans,id'],
            'label'     => ['required', 'string', 'max:100'],
            'percent'   => ['required', 'integer', 'between:1,99'],
            'starts_at' => ['required', 'date'],
            'ends_at'   => ['required', 'date', 'after:starts_at'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $rule = new NoOverlappingDiscount(
                planId:    $this->input('plan_id'),
                startsAt:  Carbon::parse($this->input('starts_at')),
                endsAt:    Carbon::parse($this->input('ends_at')),
                excludeId: null,
            );

            $rule->validate('starts_at', $this->input('starts_at'), function ($message) use ($v) {
                $v->errors()->add('starts_at', $message);
            });
        });
    }
}
