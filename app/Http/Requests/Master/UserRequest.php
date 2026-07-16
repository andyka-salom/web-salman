<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
     * Mengubah string kosong ("") menjadi NULL untuk field UUID opsional.
     */
    protected function prepareForValidation()
    {
        // Daftar field UUID yang bersifat opsional/nullable
        $nullableUuidFields = ['company_id', 'entity_function_id'];

        foreach ($nullableUuidFields as $field) {
            // Jika field ada di request dan nilainya adalah string kosong
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([
                    $field => null,
                ]);
            }
        }

        // Handle phone - jika kosong, set null
        if ($this->has('phone') && trim($this->input('phone')) === '') {
            $this->merge(['phone' => null]);
        }

        // Handle is_active (checkbox)
        $this->merge([
            'is_active' => $this->has('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Mengambil ID user dari route model binding (jika ada, yaitu saat update)
        $id = $this->route('user') ? $this->route('user')->id : null;

        // Aturan password: required saat store (ID null), nullable saat update (ID ada)
        $passwordRules = $id ? ['nullable'] : ['required'];

        return [
            'name' => ['required', 'string', 'max:255'],

            // Validasi email: harus unik, tapi abaikan user saat ini ($id)
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id)->whereNull('deleted_at')
            ],

            // Aturan password
            'password' => array_merge($passwordRules, [
                'string',
                'min:8',
                'confirmed' // Membutuhkan field 'password_confirmation'
            ]),

            // UPDATED: Phone wajib diisi
            'phone' => ['required', 'string', 'max:20'],

            'jabatan' => ['nullable', 'string', 'max:255'],

            'company_id' => ['nullable', 'uuid', 'exists:companies,id'],
            'entity_function_id' => ['nullable', 'uuid', 'exists:entity_functions,id'],

            'photo' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],

            'is_active' => ['required', 'boolean'],

            // UPDATED: Roles wajib dipilih minimal 1
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    /**
     * Custom error messages dalam Bahasa Indonesia
     */
    public function messages(): array
    {
        return [
            // Name
            'name.required' => 'Nama wajib diisi',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',

            // Email
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'email.unique' => 'Email sudah terdaftar, gunakan email lain',

            // Password
            'password.required' => 'Password wajib diisi',
            'password.string' => 'Password harus berupa teks',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',

            // Phone
            'phone.required' => 'Nomor telepon wajib diisi',
            'phone.string' => 'Nomor telepon harus berupa teks',
            'phone.max' => 'Nomor telepon maksimal 20 karakter',

            // Jabatan
            'jabatan.string' => 'Jabatan harus berupa teks',
            'jabatan.max' => 'Jabatan maksimal 255 karakter',

            // Company
            'company_id.uuid' => 'ID Company tidak valid',
            'company_id.exists' => 'Company yang dipilih tidak ditemukan',

            // Entity Function
            'entity_function_id.uuid' => 'ID Entity Function tidak valid',
            'entity_function_id.exists' => 'Entity Function yang dipilih tidak ditemukan',

            // Photo
            'photo.image' => 'File harus berupa gambar',
            'photo.max' => 'Ukuran foto maksimal 2MB',
            'photo.mimes' => 'Format foto harus: jpg, jpeg, png, atau webp',

            // Is Active
            'is_active.required' => 'Status aktif harus ditentukan',
            'is_active.boolean' => 'Status aktif harus berupa boolean',

            // Roles
            'roles.required' => 'Role wajib dipilih minimal 1',
            'roles.array' => 'Format role tidak valid',
            'roles.min' => 'Minimal pilih 1 role',
            'roles.*.exists' => 'Role yang dipilih tidak valid',
        ];
    }

    /**
     * Optional: Jika field password kosong saat update, kita hapus aturan 'confirmed'
     */
    public function withValidator($validator)
    {
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $validator->sometimes('password', 'confirmed', function ($input) {
                // Hanya jalankan aturan confirmed jika field password tidak kosong
                return !empty($input->password);
            });
        }
    }

    /**
     * Custom attribute names untuk pesan error yang lebih readable
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama',
            'email' => 'Email',
            'password' => 'Password',
            'phone' => 'Nomor Telepon',
            'jabatan' => 'Jabatan',
            'company_id' => 'Company',
            'entity_function_id' => 'Entity Function',
            'photo' => 'Foto Profil',
            'is_active' => 'Status Aktif',
            'roles' => 'Role',
        ];
    }
}
