<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'produk_id' => ['required', 'exists:produks,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
            'ukuran' => ['nullable', 'string', 'max:50'],
            'varian' => ['nullable', 'string', 'max:255'],
        ];
    }
}
