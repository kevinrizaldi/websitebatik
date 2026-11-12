<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

class MediaUrl
{
    /**
     * Resolve a Product's image (checking disk and falling back to beranda images),
     * routed through /api/media so Flutter Web receives CORS headers.
     */
    public static function fromProduct(?Product $product): ?string
    {
        if (! $product) {
            return null;
        }

        $resolved = $product->image_url;

        return self::wrapLocalUrl($resolved);
    }

    /**
     * Resolve a stored image path, external URL, or fallback by label.
     */
    public static function resolve(?string $path, ?string $fallbackLabel = null): ?string
    {
        if ($path && Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if ($path) {
            $clean = ltrim($path, '/');
            if (file_exists(public_path($clean))) {
                return url('api/media/'.$clean);
            }
            $storageRel = Str::startsWith($clean, 'storage/')
                ? Str::after($clean, 'storage/')
                : $clean;
            if (file_exists(storage_path('app/public/'.$storageRel))) {
                return url('api/media/storage/'.$storageRel);
            }
        }

        if ($fallbackLabel !== null && $fallbackLabel !== '') {
            return url('api/media/'.self::fallbackPathForLabel($fallbackLabel));
        }

        return null;
    }

    private static function wrapLocalUrl(string $url): string
    {
        $appPrefix = rtrim(url('/'), '/').'/';
        if (Str::startsWith($url, $appPrefix)) {
            $rel = ltrim(Str::after($url, $appPrefix), '/');
            if (! Str::startsWith($rel, 'api/media/')) {
                return url('api/media/'.$rel);
            }
        }

        return $url;
    }

    private static function fallbackPathForLabel(string $label): string
    {
        $haystack = strtolower($label);

        if (str_contains($haystack, 'hampers') || str_contains($haystack, 'gift') || str_contains($haystack, 'box')) {
            return 'images/beranda/gift-box.jpg';
        }
        if (str_contains($haystack, 'tas') || str_contains($haystack, 'tote') || str_contains($haystack, 'pouch')
            || str_contains($haystack, 'olahan') || str_contains($haystack, 'sisa') || str_contains($haystack, 'aksesoris')) {
            return 'images/beranda/bags-accessories.jpg';
        }
        if (str_contains($haystack, 'blouse') || str_contains($haystack, 'dress') || str_contains($haystack, 'wanita')) {
            return 'images/beranda/woman-blouse.jpg';
        }
        if (str_contains($haystack, 'kain')) {
            return 'images/beranda/cloth-fabric.jpg';
        }

        return 'images/beranda/folded-shirts.jpg';
    }
}
