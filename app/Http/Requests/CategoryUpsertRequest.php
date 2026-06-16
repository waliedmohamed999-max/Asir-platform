<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function payload(): array
    {
        return [
            'name' => $this->string('name')->toString(),
            'name_ar' => $this->string('name_ar')->toString() ?: $this->string('name')->toString(),
            'name_en' => $this->string('name_en')->toString() ?: null,
            'slug' => $this->filled('slug') ? Str::slug($this->string('slug')->toString()) : Str::slug($this->string('name')->toString()),
            'parent_id' => $this->filled('parent_id') ? $this->integer('parent_id') : null,
            'description' => $this->string('description')->toString() ?: null,
            'icon' => $this->string('icon')->toString() ?: null,
            'image_url' => $this->string('image_url')->toString() ?: null,
            'sort_order' => (int) $this->input('sort_order', 0),
            'meta_title' => $this->string('meta_title')->toString() ?: null,
            'meta_description' => $this->string('meta_description')->toString() ?: null,
            'is_active' => $this->boolean('is_active', true),
        ];
    }
}
