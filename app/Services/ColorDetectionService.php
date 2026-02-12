<?php

namespace App\Services;

use ColorThief\ColorThief;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ColorDetectionService
{
    private const SAMPLE_SIZE = 100;

    /**
     * Detect dominant color from an image file.
     *
     * @param  string  $imagePath  Absolute path to the image file.
     * @return array{family: string, hex: string, rgb: array{0: int, 1: int, 2: int}}
     *
     * @throws \InvalidArgumentException If the file does not exist or is not readable.
     * @throws \RuntimeException If the image cannot be processed or color extraction fails.
     */
    public function detect(string $imagePath): array
    {
        if (! is_string($imagePath) || $imagePath === '') {
            throw new \InvalidArgumentException('Image path must be a non-empty string.');
        }

        if (! file_exists($imagePath) || ! is_readable($imagePath)) {
            throw new \InvalidArgumentException("Image file does not exist or is not readable: {$imagePath}");
        }

        $tmpPath = null;

        try {
            $tmpPath = $this->downscaleToTemp($imagePath);
            $palette = ColorThief::getPalette($tmpPath, 5, 10, null, 'array');

            if ($palette === null || $palette === []) {
                Log::warning('ColorThief returned no palette', ['path' => $imagePath]);
                throw new \RuntimeException('Could not extract colors from image.');
            }

            $neutralFamilies = ['grey', 'white', 'black'];
            $r = $g = $b = null;
            $family = null;

            foreach ($palette as $color) {
                if (! is_array($color) || count($color) < 3) {
                    continue;
                }
                $cr = (int) $color[0];
                $cg = (int) $color[1];
                $cb = (int) $color[2];
                $cFamily = $this->classifyToFamily($cr, $cg, $cb);
                if (! in_array($cFamily, $neutralFamilies, true)) {
                    $r = $cr;
                    $g = $cg;
                    $b = $cb;
                    $family = $cFamily;
                    break;
                }
            }

            if ($r === null) {
                $first = $palette[0];
                $r = (int) $first[0];
                $g = (int) $first[1];
                $b = (int) $first[2];
                $family = $this->classifyToFamily($r, $g, $b);
            }

            $hex = $this->rgbToHex($r, $g, $b);

            return [
                'family' => $family,
                'hex' => $hex,
                'rgb' => [$r, $g, $b],
            ];
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('Color detection failed', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to process image for color detection: '.$e->getMessage(), 0, $e);
        } finally {
            if ($tmpPath !== null && file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }

    /**
     * Downscale image to SAMPLE_SIZE x SAMPLE_SIZE and save to a temporary file.
     */
    private function downscaleToTemp(string $imagePath): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imagePath);
        $image->resize(self::SAMPLE_SIZE, self::SAMPLE_SIZE);

        $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'color-thief-'.uniqid('', true).'.png';
        $image->save($tmpPath);

        if (! file_exists($tmpPath) || filesize($tmpPath) === 0) {
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
            throw new \RuntimeException('Failed to create temporary downscaled image.');
        }

        return $tmpPath;
    }

    private function rgbToHex(int $r, int $g, int $b): string
    {
        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));

        return '#'.sprintf('%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Classify RGB into a color family using HSL.
     * If saturation < 0.15 → grey (unless L < 0.1 → black or L > 0.9 → white).
     * If family would be grey but saturation > 0.2, use hue to pick a real color.
     */
    private function classifyToFamily(int $r, int $g, int $b): string
    {
        $hsl = $this->rgbToHsl($r / 255, $g / 255, $b / 255);
        $h = $hsl[0];
        $s = $hsl[1];
        $l = $hsl[2];

        if ($s < 0.15) {
            if ($l < 0.1) {
                return 'black';
            }
            if ($l > 0.9) {
                return 'white';
            }
            return 'grey';
        }

        if ($l > 0.95) {
            return 'white';
        }
        if ($l < 0.08) {
            return 'black';
        }

        $familyByHue = $this->familyFromHue($h, $s, $l);
        if ($familyByHue === 'grey' && $s > 0.2) {
            return $this->familyFromHueOnly($h);
        }

        return $familyByHue;
    }

    /**
     * Map hue (0-360) and S/L to family. Grey for very low saturation.
     */
    private function familyFromHue(float $h, float $s, float $l): string
    {
        if ($s < 0.15) {
            return 'grey';
        }

        if ($l <= 0.35 && $s <= 0.5 && (($h >= 0 && $h <= 45) || ($h >= 330 && $h <= 360))) {
            return 'brown';
        }

        if ($s <= 0.35 && $l >= 0.4 && $l <= 0.85 && $h >= 20 && $h <= 70) {
            return 'brown';
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
     * Map hue only (when we want to prefer a real color over grey).
     */
    private function familyFromHueOnly(float $h): string
    {
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
        if ($h >= 20 && $h <= 70) {
            return 'brown';
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

    /**
     * Detect from binary content (e.g. Livewire upload). Writes to temp file then calls detect().
     *
     * @return array{family: string, hex: string, rgb: array{0: int, 1: int, 2: int}}
     */
    public function detectFromBinary(string $imageContents): array
    {
        $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'color-thief-src-'.uniqid('', true).'.bin';
        if (file_put_contents($tmpPath, $imageContents) === false) {
            throw new \RuntimeException('Failed to write image data to temporary file.');
        }
        try {
            return $this->detect($tmpPath);
        } finally {
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }
}
