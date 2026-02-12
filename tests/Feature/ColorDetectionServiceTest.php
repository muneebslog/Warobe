<?php

use App\Services\ColorDetectionService;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

beforeEach(function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD PHP extension is required for ColorDetectionService tests.');
    }
    $dir = storage_path('framework/testing');
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
});

function createSolidColorImage(string $hexColor, int $width = 100, int $height = 100): string
{
    $manager = new ImageManager(new Driver());
    $image = $manager->create($width, $height);
    $image->fill($hexColor);

    $path = storage_path('framework/testing/' . uniqid('color-', true) . '.png');
    $image->save($path);

    return $path;
}

it('detects white correctly', function () {
    $path = createSolidColorImage('#ffffff');
    try {
        $result = app(ColorDetectionService::class)->detectDominantColor($path);
        expect($result['family'])->toBe('white');
        expect($result['hex'])->toStartWith('#');
    } finally {
        @unlink($path);
    }
});

it('detects black correctly', function () {
    $path = createSolidColorImage('#000000');
    try {
        $result = app(ColorDetectionService::class)->detectDominantColor($path);
        expect($result['family'])->toBe('black');
        expect($result['hex'])->toStartWith('#');
    } finally {
        @unlink($path);
    }
});

it('detects red correctly', function () {
    $path = createSolidColorImage('#ff0000');
    try {
        $result = app(ColorDetectionService::class)->detectDominantColor($path);
        expect($result['family'])->toBe('red');
        expect($result['hex'])->toStartWith('#');
    } finally {
        @unlink($path);
    }
});

it('detects blue correctly', function () {
    $path = createSolidColorImage('#0000ff');
    try {
        $result = app(ColorDetectionService::class)->detectDominantColor($path);
        expect($result['family'])->toBe('blue');
        expect($result['hex'])->toStartWith('#');
    } finally {
        @unlink($path);
    }
});

it('detects green correctly', function () {
    $path = createSolidColorImage('#00ff00');
    try {
        $result = app(ColorDetectionService::class)->detectDominantColor($path);
        expect($result['family'])->toBe('green');
        expect($result['hex'])->toStartWith('#');
    } finally {
        @unlink($path);
    }
});

it('detects low saturation grey correctly', function () {
    $path = createSolidColorImage('#808080');
    try {
        $result = app(ColorDetectionService::class)->detectDominantColor($path);
        expect($result['family'])->toBe('grey');
        expect($result['hex'])->toStartWith('#');
    } finally {
        @unlink($path);
    }
});

it('detects warm brown-ish color as brown or beige', function () {
    $path = createSolidColorImage('#965a32'); // RGB 150, 90, 50
    try {
        $result = app(ColorDetectionService::class)->detectDominantColor($path);
        expect($result['family'])->toBeIn(['brown', 'beige']);
        expect($result['hex'])->toStartWith('#');
    } finally {
        @unlink($path);
    }
});
