<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StokUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Already protected by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'adjustment_type' => ['required', 'in:add,subtract'],
            'adjustment_quantity' => ['required', 'integer', 'min:1'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'keterangan' => ['required', 'string', 'max:500']
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'adjustment_type.required' => 'Tipe penyesuaian harus dipilih',
            'adjustment_type.in' => 'Tipe penyesuaian tidak valid',
            'adjustment_quantity.required' => 'Jumlah penyesuaian harus diisi',
            'adjustment_quantity.integer' => 'Jumlah penyesuaian harus berupa angka',
            'adjustment_quantity.min' => 'Jumlah penyesuaian minimal 1',
            'stok_minimum.required' => 'Stok minimum harus diisi',
            'stok_minimum.integer' => 'Stok minimum harus berupa angka',
            'stok_minimum.min' => 'Stok minimum tidak boleh negatif',
            'keterangan.required' => 'Alasan penyesuaian stok wajib diisi untuk audit trail',
            'keterangan.max' => 'Keterangan maksimal 500 karakter'
        ];
    }
}
