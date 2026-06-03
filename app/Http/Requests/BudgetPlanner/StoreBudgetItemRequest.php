<?php

declare(strict_types=1);

namespace App\Http\Requests\BudgetPlanner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'    => ['required', 'integer', Rule::exists('wedding_budget_categories', 'id')],
            'vendor_id'         => ['nullable', 'uuid', Rule::exists('vendors', 'id')],
            // Write-through to the linked vendor (not stored on the item).
            'vendor_total_cost'  => ['nullable', 'integer', 'min:0'],
            'vendor_paid_amount' => ['nullable', 'integer', 'min:0'],
            'title'          => ['required', 'string', 'max:200'],
            'vendor_name'    => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'planned_amount' => ['nullable', 'integer', 'min:0'],
            'actual_amount'  => ['nullable', 'integer', 'min:0'],
            'dp_amount'      => ['nullable', 'integer', 'min:0'],
            'dp_paid'        => ['sometimes', 'boolean'],
            'final_amount'   => ['nullable', 'integer', 'min:0'],
            'final_paid'     => ['sometimes', 'boolean'],
            'due_date'       => ['nullable', 'date'],
            'payment_status' => ['sometimes', Rule::in(['unpaid', 'dp', 'paid'])],
            'payment_date'   => ['nullable', 'date'],
            'invitation_id'  => ['nullable', 'integer', 'exists:invitations,id'],
        ];
    }
}
