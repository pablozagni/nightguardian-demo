<?php

namespace App\Support;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * An inline SVG QR code for a link (pure PHP, no image extension needed). Cached: the same link gives the same code.
 */
final class Qr
{
    public static function svg(string $url): ?string
    {
        try {
            return Cache::rememberForever('qr:'.md5($url), function () use ($url): string {
                $options = new QROptions([
                    'outputInterface' => QRMarkupSVG::class,
                    'outputBase64' => false,
                    'addQuietzone' => true,
                    'svgAddXmlHeader' => false,
                    'drawLightModules' => false,
                    'svgUseFillAttributes' => true,
                    'markupDark' => '#0a0f1f',
                    'markupLight' => '#ffffff',
                    'eccLevel' => EccLevel::M,
                ]);

                return (new QRCode($options))->render($url);
            });
        } catch (Throwable) {
            return null;
        }
    }
}
