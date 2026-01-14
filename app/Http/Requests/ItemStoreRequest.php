<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemStoreRequest extends FormRequest
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
            // kode tidak perlu diinput, akan di-generate otomatis
            'kode' => ['nullable', 'string', 'max:255', 'unique:items,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:item_categories,id'],
            'satuan_id' => ['required', 'exists:item_units,id'],
            'tipe' => ['required', 'in:mekanik,listrik'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
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
            'kode.unique' => 'Kode item sudah digunakan',
            'kode.max' => 'Kode item maksimal 255 karakter',
            'nama.required' => 'Nama item harus diisi',
            'nama.max' => 'Nama item maksimal 255 karakter',
            'kategori_id.required' => 'Kategori harus dipilih',
            'kategori_id.exists' => 'Kategori tidak valid',
            'satuan_id.required' => 'Satuan harus dipilih',
            'satuan_id.exists' => 'Satuan tidak valid',
            'tipe.required' => 'Tipe barang harus dipilih',
            'tipe.in' => 'Tipe barang harus Mekanik atau Listrik',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter',
        ];
    }
}
