<?php

declare(strict_types=1);

namespace Millancore\Vite\Tests\Functional;

use Millancore\Vite\ViteException;
use Millancore\Vite\ViteManager;
use Millancore\Vite\ViteManifestNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ViteManager::class)]
class ViteManagerTest extends TestCase
{
    private string $fixturesPath = __DIR__.'/../Fixtures';
    private string $distPath;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a dummy dist directory for testing
        $this->distPath = $this->fixturesPath.'/.dist';
        if (!is_dir($this->distPath)) {
            mkdir($this->distPath);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_dir($this->distPath)) {
            rmdir($this->distPath);
        }
    }

    private function getManager(string $manifestName = 'manifest.json'): ViteManager
    {
        return new ViteManager(
            dist: $this->distPath,
            customManifest: "{$this->fixturesPath}/{$manifestName}",
        );
    }

    public function test_it_throws_exception_if_manifest_not_found(): void
    {
        $this->expectException(ViteManifestNotFoundException::class);
        $this->getManager('non-existent-manifest.json')->getManifest();
    }

    public function test_it_throws_exception_for_invalid_json(): void
    {
        $this->expectException(ViteException::class);
        $this->expectExceptionMessage('Invalid JSON');
        $this->getManager('manifest-invalid.json')->getManifest();
    }

    public function test_it_gets_asset_path_in_production_mode(): void
    {
        $manager = $this->getManager();
        // Assuming dev server is not running
        $this->assertStringContainsString('dist/assets/main.12345.js', $manager->get('main.js'));
    }

    public function test_it_throws_exception_for_missing_asset_in_production(): void
    {
        $manager = $this->getManager();
        $this->expectException(ViteException::class);
        $this->expectExceptionMessage('Asset for file "non-existent.js" not found');
        $manager->get('non-existent.js');
    }

    public function test_it_gets_styles_for_an_entry(): void
    {
        $manager = $this->getManager();
        $expected = [basename($this->distPath).'/assets/main.67890.css'];
        $this->assertSame($expected, $manager->styles('main.js'));
    }

    public function test_styles_returns_empty_array_if_css_is_empty(): void
    {
        $manager = $this->getManager();
        $this->assertSame([], $manager->styles('entry-no-css.js'));
    }

    public function test_styles_returns_null_if_css_is_empty(): void
    {
        $manager = $this->getManager();
        $this->assertNull($manager->styles('entry-no-css.js'));
    }

    public function test_add_react_refresh_returns_null_when_dev_server_is_off(): void
    {
        $manager = $this->getManager();
        // In a test environment, isRunningDevServer() will be false.
        $this->assertNull($manager->addReactRefresh());
    }
}
