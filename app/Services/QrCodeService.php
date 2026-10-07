<?php

namespace App\Services;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    public static function svgDataUri(string $text): ?string
    {
        try {
            $writer = new Writer(new ImageRenderer(new RendererStyle(256), new SvgImageBackEnd()));

            return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($text));
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function pngFile(string $text, int $size = 512): ?string
    {
        try {
            $writer = new Writer(new GDLibRenderer($size, 4, 'png', 9));
            $png = $writer->writeString($text);
            $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'silapin_qr_' . uniqid('', true) . '.png';

            if (@file_put_contents($path, $png) === false) {
                return null;
            }

            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
