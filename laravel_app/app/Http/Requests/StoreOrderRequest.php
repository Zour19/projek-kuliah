<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama pelanggan harus diisi',
            'customer_email.required' => 'Email harus diisi',
            'customer_email.email' => 'Format email tidak valid',
            'customer_phone.required' => 'Nomor telepon harus diisi',
            'delivery_address.required' => 'Alamat pengiriman harus diisi',
            'items.required' => 'Produk tidak boleh kosong',
            'items.*.product_id.exists' => 'Produk tidak ditemukan',
            'items.*.quantity.min' => 'Jumlah minimal 1',
        ];
    }
}
