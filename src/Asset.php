<?php

declare(strict_types=1);

namespace Millancore\Vite;

readonly class Asset
{
    /**
     * @param array<string> $imports
     * @param array<string> $css
     */
    public function __construct(
        public string $origin,
        public string $file,
        public ?string $name = null,
        public ?string $src = null,
        public ?bool $isEntry = false,
        public array $imports = [],
        public array $css = [],
    ) {
    }

    /**
     * @param array<mixed> $rawAsset
     */
    public static function fromArray(string $origin, array $rawAsset): self
    {
        return new self(
            $origin,
            $rawAsset['file'],
            $rawAsset['name'] ?? null,
            $rawAsset['src'] ?? null,
            $rawAsset['isEntry'] ?? false,
            $rawAsset['imports'] ?? [],
            $rawAsset['css'] ?? [],
        );
    }
}
