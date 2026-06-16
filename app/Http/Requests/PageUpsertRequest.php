<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Page;

class PageUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $pageId = $this->route('page')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($pageId)],
            'footer_group' => ['nullable', Rule::in(array_keys(Page::FOOTER_GROUPS))],
            'footer_label' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'show_in_footer' => ['nullable', 'boolean'],
            'target_url' => ['nullable', 'string', 'max:2048'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function payload(): array
    {
        return [
            'title' => $this->string('title')->toString(),
            'slug' => $this->filled('slug') ? Str::slug($this->string('slug')->toString()) : Str::slug($this->string('title')->toString()),
            'footer_group' => $this->string('footer_group')->toString() ?: null,
            'footer_label' => $this->string('footer_label')->toString() ?: null,
            'excerpt' => $this->string('excerpt')->toString() ?: null,
            'body' => $this->string('body')->toString() ?: null,
            'meta_title' => $this->string('meta_title')->toString() ?: null,
            'meta_description' => $this->string('meta_description')->toString() ?: null,
            'sort_order' => (int) $this->input('sort_order', 0),
            'show_in_footer' => $this->boolean('show_in_footer'),
            'target_url' => $this->string('target_url')->toString() ?: null,
            'open_in_new_tab' => $this->boolean('open_in_new_tab'),
            'is_active' => $this->boolean('is_active', true),
        ];
    }
}
