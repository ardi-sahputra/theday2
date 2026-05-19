<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateWorkHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'timezone' => 'required|string|max:64',
            'days'     => 'required|array|min:1',
            'days.*'   => 'integer|between:1,7',
            'start'    => 'required|date_format:H:i',
            'end'      => 'required|date_format:H:i|after:start',
        ];
    }
}
