<?php

namespace App\Services\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaUploadService
{
    public function storeImage(UploadedFile $file, string $directory): string
    {
        $relativeDirectory = 'uploads/'.trim($directory, '/');
        $absoluteDirectory = public_path($relativeDirectory);

        if (! is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0777, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = now()->format('YmdHis').'-'.Str::random(12).'.'.$extension;

        $file->move($absoluteDirectory, $filename);

        return asset($relativeDirectory.'/'.$filename);
    }

    public function storeMany(array $files, string $directory): array
    {
        return collect($files)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->map(fn (UploadedFile $file) => $this->storeImage($file, $directory))
            ->values()
            ->all();
    }

    public function deleteIfManaged(?string $url): void
    {
        if (blank($url)) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';

        if (! Str::contains($path, '/uploads/')) {
            return;
        }

        $relativePath = ltrim(Str::after($path, '/uploads/'), '/');
        $absolutePath = public_path('uploads/'.$relativePath);

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}
