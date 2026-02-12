<?php

namespace App\Services;

use Intervention\Image\Colors\Rgb\Color as RgbColor;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Interfaces\ImageInterface;

class ColorDetectionService
{
    private const SAMPLE_SIZE = 50;

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
     * Sample center of image and map average RGB to hex + color family.
     * Ignores near-white, near-black and very low-saturation pixels so the
     * garment color is detected instead of the background.
     *
     * @param  ImageInterface  $image
     * @return array{hex: string, family: string}
     */
    private function sampleAndMap(ImageInterface $image): array
    {
        $image->resize(self::SAMPLE_SIZE, self::SAMPLE_SIZE);

        $width = $image->width();
        $height = $image->height();

        $totalR = $totalG = $totalB = 0;
        $count = 0;
        $totalRAll = $totalGAll = $totalBAll = 0;
        $countAll = 0;

        $yStart = (int) floor($height * 0.2);
        $yEnd = (int) ceil($height * 0.8);
        $xStart = (int) floor($width * 0.2);
        $xEnd = (int) ceil($width * 0.8);

        for ($y = $yStart; $y < $yEnd; $y++) {
            for ($x = $xStart; $x < $xEnd; $x++) {
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
                    if ($l >= 0.92 || $l <= 0.08 || $s <= 0.12) {
                        continue;
                    }
                    $totalR += $r;
                    $totalG += $g;
                    $totalB += $b;
                    $count++;
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        if ($count > 0) {
            $r = (int) round($totalR / $count);
            $g = (int) round($totalG / $count);
            $b = (int) round($totalB / $count);
        } elseif ($countAll > 0) {
            $r = (int) round($totalRAll / $countAll);
            $g = (int) round($totalGAll / $countAll);
            $b = (int) round($totalBAll / $countAll);
        } else {
            return ['hex' => '#808080', 'family' => 'grey'];
        }

        $hex = sprintf('#%02x%02x%02x', $r, $g, $b);
        $family = $this->rgbToFamily($r, $g, $b);

        return ['hex' => $hex, 'family' => $family];
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
