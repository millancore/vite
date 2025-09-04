<?php

declare(strict_types=1);

namespace Millancore\Vite;

class ViteManager
{
    private ?Manifest $manifest = null;
    private ?bool $isDevServerRunning = null;
    private string $defaultManifestName = 'manifest.json';
    private string $defaultManifestFolder = '.vite';

    private string $distBasePath;

    /**
     * @throws ViteManifestNotFoundException
     */
    public function __construct(
        public readonly string $dist,
        public readonly ?string $customManifest = null,
        public readonly string $host = 'localhost',
        public readonly int $port = 5173,
    ) {
        $this->distBasePath = basename($dist);
    }

    /**
     *  Is possible to load a manifest from a different path
     *  1. Using the exact path to the manifest
     *  2. Using the root path where exists .vite folder.
     *
     * @throws ViteManifestNotFoundException
     */
    public function loadManifest(?string $path = null): void
    {
        if (!file_exists($path)) {
            $this->notFoundManifest($path);
        }

        if (is_dir($path)) {
            $path = $path.'/'.$this->defaultManifestFolder.'/'.$this->defaultManifestName;

            if (!file_exists($path)) {
                $this->notFoundManifest($path);
            }
        }

        $this->manifest = Manifest::fromArray($this->resolveManifest($path));
    }

    /**
     * @throws ViteManifestNotFoundException
     */
    private function notFoundManifest(string $path): void
    {
        throw new ViteManifestNotFoundException("Manifest not found at: $path");
    }

    public function getManifest(): Manifest
    {
        if ($this->manifest === null) {
            $this->loadManifest($this->customManifest ?? $this->dist);
        }

        return $this->manifest;
    }

    /**
     * @return array<mixed>
     *
     * @throws ViteException
     */
    private function resolveManifest(string $manifestPath): array
    {
        $manifestData = json_decode(file_get_contents($manifestPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ViteException("Invalid JSON in manifest file: $manifestPath");
        }

        return $manifestData;
    }

    /**
     * @throws ViteException
     */
    public function get(string $filePath): string
    {
        if ($this->isRunningDevServer()) {
            return sprintf('http://%s:%s/%s', $this->host, $this->port, $filePath);
        }

        if (!$this->getManifest()->hasAssetByFile($filePath)) {
            throw new ViteException(sprintf('Asset for file "%s" not found', $filePath));
        }

        return $this->distBasePath.'/'.$this->getManifest()->getAssetByFile($filePath)->file;
    }

    /**
     * @return string[]
     */
    public function styles(string $filePath): array
    {
        $asset = $this->getManifest()->getAssetByFile($filePath);

        if (!$asset || empty($asset->css)) {
            return [];
        }

        return array_map(fn ($cssFile) => $this->distBasePath.'/'.$cssFile, $asset->css);
    }

    public function addReactRefresh(): ?string
    {
        if (!$this->isRunningDevServer()) {
            return null;
        }

        $refreshScript = file_get_contents(__DIR__.'/react-refresh.html');

        return str_replace('{port}', "$this->port", $refreshScript);
    }

    public function isRunningDevServer(): bool
    {
        if ($this->isDevServerRunning !== null) {
            return $this->isDevServerRunning;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, sprintf('http://%s:%s', $this->host, $this->port));
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);

        curl_close($ch);

        return $this->isDevServerRunning = ($response === true);
    }
}
