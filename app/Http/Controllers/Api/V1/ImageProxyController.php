<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ImageProxyController extends Controller
{
    public function __invoke(Request $request)
    {
        $url = $request->query('url');
        abort_unless(is_string($url) && preg_match('/^https?:\/\//', $url), 404);

        if ($localPath = $this->localPublicPath($url)) {
            return $this->fileResponse($localPath);
        }

        return redirect()->away($url)
            ->header('Cache-Control', 'public, max-age=86400')
            ->header('Access-Control-Allow-Origin', '*');
    }

    private function localPublicPath(string $url): ?string
    {
        $parts = parse_url($url);

        if (! $parts || ! isset($parts['host'], $parts['path'])) {
            return null;
        }

        $requestHost = request()->getHost();
        $isLocalHost = in_array($parts['host'], [$requestHost, '127.0.0.1', 'localhost'], true);

        if (! $isLocalHost) {
            return null;
        }

        $path = public_path(ltrim($parts['path'], '/'));
        $realPath = realpath($path);
        $publicPath = realpath(public_path());

        return $realPath && $publicPath && File::exists($path) && str_starts_with($realPath, $publicPath)
            ? $path
            : null;
    }

    private function fileResponse(string $path)
    {
        return response(File::get($path), 200)
            ->header('Content-Type', File::mimeType($path) ?: 'image/png')
            ->header('Cache-Control', 'public, max-age=86400')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
