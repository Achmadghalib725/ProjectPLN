<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemUpdateRequest extends FormRequest
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
        $itemId = $this->route('item'); // Get ID from route parameter

        return [
            'kode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('items', 'kode')->ignore($itemId)
            ],
            'nama' => ['required', 'string', 'max:255'],
            'satuan' => ['required', 'string', 'max:50'],
            'kategori' => ['required', 'string', 'max:100'],
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
            'kode.required' => 'Kode item harus diisi',
            'kode.unique' => 'Kode item sudah digunakan, gunakan kode lain',
            'kode.max' => 'Kode item maksimal 50 karakter',
            'nama.required' => 'Nama item harus diisi',
            'nama.max' => 'Nama item maksimal 255 karakter',
            'satuan.required' => 'Satuan harus diisi',
            'satuan.max' => 'Satuan maksimal 50 karakter',
            'kategori.required' => 'Kategori harus diisi',
            'kategori.max' => 'Kategori maksimal 100 karakter',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter',
        ];
    }
}
