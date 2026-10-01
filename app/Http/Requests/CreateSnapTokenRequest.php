<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSnapTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller.
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'ID pesanan wajib diisi.',
            'order_id.integer' => 'ID pesanan harus berupa angka.',
        ];
    }
}
