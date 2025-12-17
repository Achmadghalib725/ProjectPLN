<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StokStoreRequest extends FormRequest
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
            'item_id' => [
                'required',
                'exists:items,id',
                // Prevent duplicate: item already in this warehouse
                Rule::unique('item_stocks')
                    ->where('gudang_id', $this->user()->gudang_id)
            ],
            'jumlah' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:500']
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
            'item_id.required' => 'Item harus dipilih',
            'item_id.exists' => 'Item yang dipilih tidak valid',
            'item_id.unique' => 'Item ini sudah ada di gudang Anda',
            'jumlah.required' => 'Jumlah stok harus diisi',
            'jumlah.integer' => 'Jumlah stok harus berupa angka',
            'jumlah.min' => 'Jumlah stok tidak boleh negatif',
            'stok_minimum.required' => 'Stok minimum harus diisi',
            'stok_minimum.integer' => 'Stok minimum harus berupa angka',
            'stok_minimum.min' => 'Stok minimum tidak boleh negatif',
            'keterangan.max' => 'Keterangan maksimal 500 karakter'
        ];
    }
}
