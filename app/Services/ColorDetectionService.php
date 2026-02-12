<?php

namespace App\Services;

use Intervention\Image\Colors\Rgb\Color as RgbColor;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Detects dominant color from images. Tuned for typical at-home clothing photos:
 * items on bed/floor/hanger, phone camera, indoor lighting, light/white backgrounds.
 */
class ColorDetectionService
{
    /** Resize to this before sampling. Slightly larger = more stable for phone pics. */
    private const SAMPLE_SIZE = 64;

    /** Exclude pixels lighter than this (white/cream backgrounds). */
    private const LIGHT_THRESHOLD = 0.90;

    /** Exclude pixels darker than this (pure black/shadows). */
    private const DARK_THRESHOLD = 0.05;

    /** Exclude pixels with saturation below this (grey/neutral). */
    private const MIN_SATURATION = 0.05;

    /**
     * Detect dominant color from image binary (e.g. file contents).
     * Use this when you have raw image data to avoid temp file / path issues.
     *
     * @return array{hex: string, family: string}
     */
    public function detectDominantColorFromBinary(string $imageContents): array
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imageContents);
        return $this->sampleAndMap($image);
    }

    /**
     * Detect dominant color from image file path.
     *
     * @return array{hex: string, family: string}
     */
    public function detectDominantColor(string $imagePath): array
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imagePath);
        return $this->sampleAndMap($image);
    }

    /**
     * Sample the whole image and pick the dominant color family (most frequent
     * hue), then average RGB of that family only. Full-frame sampling so we
     * still get the garment when it's off-center (e.g. shirt on left, background right).
     * Ignores white/cream, near-black, and grey so background doesn't dominate.
     *
     * @param  ImageInterface  $image
     * @return array{hex: string, family: string}
     */
    private function sampleAndMap(ImageInterface $image): array
    {
        $image->resize(self::SAMPLE_SIZE, self::SAMPLE_SIZE);

        $width = $image->width();
        $height = $image->height();

        /** @var array<string, array<int, array{0: int, 1: int, 2: int}>> $byFamily */
        $byFamily = [];
        $totalRAll = $totalGAll = $totalBAll = 0;
        $countAll = 0;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                try {
                    $color = $image->pickColor($x, $y);
                    if ($color->isClear()) {
                        continue;
                    }
                    $rgb = $color->convertTo(RgbColor::class);
                    $r = $rgb->red()->value();
                    $g = $rgb->green()->value();
                    $b = $rgb->blue()->value();

                    $totalRAll += $r;
                    $totalGAll += $g;
                    $totalBAll += $b;
                    $countAll++;

                    $hsl = $this->rgbToHsl($r / 255, $g / 255, $b / 255);
                    $l = $hsl[2];
                    $s = $hsl[1];
                    if ($l >= self::LIGHT_THRESHOLD || $l <= self::DARK_THRESHOLD || $s <= self::MIN_SATURATION) {
                        continue;
                    }

                    $family = $this->rgbToFamily($r, $g, $b);
                    if (! isset($byFamily[$family])) {
                        $byFamily[$family] = [];
                    }
                    $byFamily[$family][] = [$r, $g, $b];
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        if (count($byFamily) > 0) {
            $neutralFamilies = ['grey', 'white', 'black'];
            $byCount = [];
            foreach ($byFamily as $family => $pixels) {
                $byCount[$family] = count($pixels);
            }
            arsort($byCount, SORT_NUMERIC);
            $dominantFamily = null;
            foreach (array_keys($byCount) as $family) {
                if (! in_array($family, $neutralFamilies, true)) {
                    $dominantFamily = $family;
                    break;
                }
            }
            if ($dominantFamily === null) {
                $dominantFamily = array_key_first($byCount);
            }
            $pixels = $byFamily[$dominantFamily];
            $totalR = $totalG = $totalB = 0;
            foreach ($pixels as $rgb) {
                $totalR += $rgb[0];
                $totalG += $rgb[1];
                $totalB += $rgb[2];
            }
            $n = count($pixels);
            $r = (int) round($totalR / $n);
            $g = (int) round($totalG / $n);
            $b = (int) round($totalB / $n);
            $hex = sprintf('#%02x%02x%02x', $r, $g, $b);
            return ['hex' => $hex, 'family' => $dominantFamily];
        }

        if ($countAll > 0) {
            $r = (int) round($totalRAll / $countAll);
            $g = (int) round($totalGAll / $countAll);
            $b = (int) round($totalBAll / $countAll);
            $hex = sprintf('#%02x%02x%02x', $r, $g, $b);
            $family = $this->rgbToFamily($r, $g, $b);
            return ['hex' => $hex, 'family' => $family];
        }

        return ['hex' => '#808080', 'family' => 'grey'];
    }

    /**
     * Convert RGB (0-255) to color family via HSL.
     */
    private function rgbToFamily(int $r, int $g, int $b): string
    {
        $hsl = $this->rgbToHsl($r / 255, $g / 255, $b / 255);
        $h = $hsl[0]; // 0-360
        $s = $hsl[1]; // 0-1
        $l = $hsl[2]; // 0-1

        if ($s <= 0.12) {
            if ($l >= 0.92) {
                return 'white';
            }
            if ($l <= 0.15) {
                return 'black';
            }
            return 'grey';
        }

        if ($l >= 0.92) {
            return 'white';
        }
        if ($l <= 0.12) {
            return 'black';
        }

        if ($s <= 0.35 && $l >= 0.4 && $l <= 0.85) {
            if ($h >= 20 && $h <= 70) {
                return 'beige';
            }
        }

        if ($l <= 0.35 && $s <= 0.5) {
            if (($h >= 0 && $h <= 45) || ($h >= 330 && $h <= 360)) {
                return 'brown';
            }
        }

        if ($h >= 291 && $h < 345) {
            return 'pink';
        }
        if ($h >= 251 && $h < 291) {
            return 'purple';
        }
        if ($h >= 161 && $h < 251) {
            return 'blue';
        }
        if ($h >= 71 && $h < 161) {
            return 'green';
        }
        if ($h >= 46 && $h < 71) {
            return 'yellow';
        }
        if ($h >= 16 && $h < 46) {
            return 'orange';
        }
        if (($h >= 0 && $h <= 15) || ($h >= 345 && $h <= 360)) {
            return 'red';
        }

        return 'grey';
    }

    /**
     * @return array{0: float, 1: float, 2: float} [h 0-360, s 0-1, l 0-1]
     */
    private function rgbToHsl(float $r, float $g, float $b): array
    {
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        switch ($max) {
            case $r:
                $h = (($g - $b) / $d) + ($g < $b ? 6 : 0);
                break;
            case $g:
                $h = (($b - $r) / $d) + 2;
                break;
            default:
                $h = (($r - $g) / $d) + 4;
        }
        $h = $h / 6 * 360;
        if ($h < 0) {
            $h += 360;
        }

        return [$h, $s, $l];
    }
}
