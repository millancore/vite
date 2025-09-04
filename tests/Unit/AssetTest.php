<?php

declare(strict_types=1);

namespace Millancore\Vite\Tests\Unit;

use Millancore\Vite\Asset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Asset::class)]
class AssetTest extends TestCase
{
    public function test_it_can_be_created_from_array(): void
    {
        $rawAsset = [
            'file' => 'assets/main.12345.js',
            'src' => 'main.js',
            'isEntry' => true,
            'css' => ['assets/main.67890.css'],
        ];

        $asset = Asset::fromArray('main.js', $rawAsset);

        $this->assertSame('main.js', $asset->origin);
        $this->assertSame('assets/main.12345.js', $asset->file);
        $this->assertSame('main.js', $asset->src);
        $this->assertTrue($asset->isEntry);
        $this->assertSame(['assets/main.67890.css'], $asset->css);
        $this->assertNull($asset->name);
    }

    /**
     * @covers \Millancore\Vite\Asset
     */
    public function test_it_handles_optional_attributes(): void
    {
        $rawAsset = [
            'file' => 'assets/style.abcde.css',
            'src' => 'style.css',
        ];

        $asset = Asset::fromArray('style.css', $rawAsset);

        $this->assertSame('style.css', $asset->origin);
        $this->assertSame('assets/style.abcde.css', $asset->file);
        $this->assertSame('style.css', $asset->src);
        $this->assertFalse($asset->isEntry);
        $this->assertSame([], $asset->css);
    }
}
