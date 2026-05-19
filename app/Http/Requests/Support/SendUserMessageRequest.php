<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class SendUserMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $cfg = config('support.attachment');
        return [
            'body'  => 'required_without:image|nullable|string|max:5000',
            'image' => [
                'sometimes',
                'file',
                'image',
                'mimes:jpeg,png,webp',
                "max:{$cfg['max_size_kb']}",
            ],
        ];
    }
}
