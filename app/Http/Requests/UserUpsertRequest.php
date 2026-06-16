<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(array_keys(User::availableRoles()))],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'logo_url' => ['nullable', 'url'],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_logo' => ['nullable', 'boolean'],
            'bio' => ['nullable', 'string'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'instagram_url' => ['nullable', 'url'],
            'x_url' => ['nullable', 'url'],
            'website_url' => ['nullable', 'url'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function payload(): array
    {
        $payload = [
            'name' => $this->string('name')->toString(),
            'email' => $this->string('email')->toString(),
            'phone' => $this->string('phone')->toString() ?: null,
            'role' => $this->string('role')->toString(),
            'logo_url' => $this->string('logo_url')->toString() ?: null,
            'bio' => $this->string('bio')->toString() ?: null,
            'whatsapp' => $this->string('whatsapp')->toString() ?: null,
            'instagram_url' => $this->string('instagram_url')->toString() ?: null,
            'x_url' => $this->string('x_url')->toString() ?: null,
            'website_url' => $this->string('website_url')->toString() ?: null,
            'is_active' => $this->boolean('is_active', true),
        ];

        if ($this->filled('password')) {
            $payload['password'] = $this->string('password')->toString();
        }

        return $payload;
    }
}
