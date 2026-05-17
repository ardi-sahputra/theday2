<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user('admin') === null) {
            return false;
        }

        $plan = $this->route('plan');

        return $plan !== null && $plan->slug === 'premium';
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:100'],
            'price'              => ['required', 'integer', 'min:0'],
            'duration_days'      => ['required', 'integer', 'min:1', 'max:3650'],
            'max_invitations'    => ['required', 'integer', 'min:0'],
            'max_gallery_photos' => ['required', 'integer', 'min:1'],
            'custom_music'       => ['required', 'boolean'],
            'remove_watermark'   => ['required', 'boolean'],
            'custom_domain'      => ['required', 'boolean'],
            'analytics_access'   => ['required', 'boolean'],
            'features'           => ['required', 'array', 'min:1'],
            'features.*'         => ['required', 'string', 'max:100'],
            'is_active'          => ['required', 'boolean'],
        ];
    }
}
