<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * On-demand thumbnail generator using GD.
 *
 * Caches resized JPEGs under storage/app/public/thumbs/w{width}/{path}.jpg
 * so the second hit is served straight from disk. Long-lived Cache-Control
 * lets browsers avoid re-downloading during a report session.
 */
class ThumbController extends Controller
{
    /** Allowed widths — keeps disk usage bounded */
    private const ALLOWED_WIDTHS = [400, 600, 800, 1200, 1600];

    public function show(Request $request)
    {
        $path = (string) $request->query('path', '');
        $w    = (int)    $request->query('w', 800);

        // Basic guard against path traversal / absolute paths
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, 'thumbs/')) {
            abort(404);
        }

        // Snap requested width to the closest allowed size
        $w = collect(self::ALLOWED_WIDTHS)
            ->sortBy(fn ($x) => abs($x - $w))
            ->first();

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            abort(404);
        }

        $srcAbs = $disk->path($path);
        $mime   = @mime_content_type($srcAbs) ?: 'application/octet-stream';

        // Non-raster or unknown types: just stream the original
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            return response()->file($srcAbs, [
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        $thumbRel = "thumbs/w{$w}/" . preg_replace('/\.(jpe?g|png|webp|gif)$/i', '.jpg', $path);
        $thumbAbs = $disk->path($thumbRel);

        if (!file_exists($thumbAbs) || filemtime($thumbAbs) < filemtime($srcAbs)) {
            $this->makeThumb($srcAbs, $thumbAbs, $w);
        }

        return response()->file($thumbAbs, [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function makeThumb(string $src, string $dst, int $maxW): void
    {
        $info = @getimagesize($src);
        if (!$info) {
            abort(500, 'invalid image');
        }
        [$srcW, $srcH, $type] = $info;

        $img = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($src) : null,
            IMAGETYPE_GIF  => imagecreatefromgif($src),
            default        => null,
        };
        if (!$img) {
            abort(500, 'unsupported image type');
        }

        // Auto-rotate JPEGs based on EXIF orientation (phones)
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
            $ori  = (int) ($exif['Orientation'] ?? 1);
            if ($ori === 3) {
                $img = imagerotate($img, 180, 0);
            } elseif ($ori === 6) {
                $img = imagerotate($img, -90, 0);
                [$srcW, $srcH] = [$srcH, $srcW];
            } elseif ($ori === 8) {
                $img = imagerotate($img, 90, 0);
                [$srcW, $srcH] = [$srcH, $srcW];
            }
        }

        if ($srcW <= $maxW) {
            $newW = $srcW;
            $newH = $srcH;
        } else {
            $newW = $maxW;
            $newH = (int) round($srcH * ($maxW / $srcW));
        }

        $dstImg = imagecreatetruecolor($newW, $newH);
        // Flatten transparency onto white
        $white = imagecolorallocate($dstImg, 255, 255, 255);
        imagefilledrectangle($dstImg, 0, 0, $newW, $newH, $white);
        imagecopyresampled($dstImg, $img, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

        if (!is_dir(dirname($dst))) {
            @mkdir(dirname($dst), 0775, true);
        }
        imagejpeg($dstImg, $dst, 82);

        imagedestroy($img);
        imagedestroy($dstImg);
    }
}
