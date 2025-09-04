<?php

declare(strict_types=1);

namespace Millancore\Vite\Tests\Unit;

use Millancore\Vite\Asset;
use Millancore\Vite\Manifest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Manifest::class)]
#[UsesClass(Asset::class)]
class ManifestTest extends TestCase
{
    public function test_it_can_be_created_from_array(): void
    {
        $rawManifest = [
            'main.js' => [
                'file' => 'assets/main.12345.js',
                'src' => 'main.js',
            ],
        ];

        $manifest = Manifest::fromArray($rawManifest);
        $assets = $manifest->getAssets();

        $this->assertCount(1, $assets);
        $this->assertInstanceOf(Asset::class, $assets[0]);
        $this->assertSame('main.js', $assets[0]->origin);
    }

    public function test_it_can_get_asset_by_file(): void
    {
        $manifest = Manifest::fromArray([
            'main.js' => ['file' => 'assets/main.js'],
        ]);

        $asset = $manifest->getAssetByFile('main.js');
        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertSame('main.js', $asset->origin);

        $this->assertNull($manifest->getAssetByFile('non-existent.js'));
    }

    /**
     * @covers \Millancore\Vite\Manifest
     */
    public function test_it_can_check_if_asset_exists(): void
    {
        $manifest = Manifest::fromArray([
            'main.js' => ['file' => 'assets/main.js'],
        ]);

        $this->assertTrue($manifest->hasAssetByFile('main.js'));
        $this->assertFalse($manifest->hasAssetByFile('non-existent.js'));
    }
}
