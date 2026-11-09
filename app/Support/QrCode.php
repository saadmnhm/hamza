<?php

namespace App\Support;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Centralised QR code generation. Returns an inline SVG string or a data URI.
 */
final class QrCode
{
    public static function svg(string $content, int $size = 120): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 2, SquareModule::instance(), null, Fill::uniformColor(new Rgb(0, 0, 0), new Rgb(255, 255, 255))),
            new SvgImageBackEnd,
        );

        $writer = new Writer($renderer);

        return $writer->writeString($content);
    }

    public static function dataUri(string $content, int $size = 120): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode(self::svg($content, $size));
    }
}
