<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    /**
     * Serve local public or storage images through Laravel so CORS headers apply.
     */
    public function show(string $path): BinaryFileResponse
    {
        $clean = ltrim(str_replace('\\', '/', $path), '/');
        if ($clean === '' || str_contains($clean, '..')) {
            abort(404);
        }

        if (Str::startsWith($clean, 'storage/')) {
            $rel = Str::after($clean, 'storage/');
            $baseDir = realpath(storage_path('app/public'));
            $target = $baseDir ? realpath($baseDir.DIRECTORY_SEPARATOR.$rel) : false;
        } else {
            $baseDir = realpath(public_path());
            $target = $baseDir ? realpath($baseDir.DIRECTORY_SEPARATOR.$clean) : false;
        }

        if (! $baseDir || ! $target || ! is_file($target) || ! Str::startsWith($target, $baseDir)) {
            abort(404);
        }

        return response()->file($target, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

