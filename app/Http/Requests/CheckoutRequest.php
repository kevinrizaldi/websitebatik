<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('notes') && trim((string) $this->input('notes')) === '') {
            $this->merge(['notes' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255', 'regex:/^[\pL0-9\s\'.()\-]+$/u'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\- ]{10,20}$/'],
            'address' => ['required', 'string', 'max:1000', 'regex:/^[\pL0-9\s.,\/\-()\[\]#&\'":;]+$/u'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'shipping_option' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500', 'regex:/^[\pL0-9\s.,\'\-()\/!?]+$/u'],
            'alamat_id' => ['nullable', 'integer'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama penerima wajib diisi.',
            'customer_name.regex' => 'Nama penerima hanya boleh berupa huruf, spasi, titik, atau tanda petik.',
            'phone.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'phone.regex' => 'Nomor WhatsApp / HP harus berupa nomor telepon yang valid.',
            'address.required' => 'Alamat pengiriman wajib diisi.',
            'address.regex' => 'Alamat pengiriman tidak boleh mengandung simbol khusus yang tidak valid.',
            'notes.regex' => 'Catatan pesanan mengandung simbol yang tidak diperbolehkan.',
        ];
    }
}
