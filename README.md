# PHP Vite Manager

A PHP library for managing Vite assets in your application. It helps you load assets from the Vite development server or your production build directory, based on the `manifest.json` file.

## Installation

Install the package via Composer:

```bash
composer require millancore/vite
```

## Basic Usage

1.  **Initialize the ViteManager:**

    Create an instance of `ViteManager`, providing the path to your distribution (build) directory.

    ```php
    use Millancore\Vite\ViteManager;

    // Path to your public/dist directory where Vite builds the assets
    $distPath = __DIR__ . '/public/dist';

    $vite = new ViteManager($distPath);
    ```

2.  **Get Asset URLs:**

    Use the `get()` method to retrieve the correct URL for an asset, whether you are in a development or production environment.

    ```php
    // In your template or view file
    <script src="<?= $vite->get('main.js') ?>" type="module"></script>
    ```

    *   **In Development:** This will output the Vite dev server URL (e.g., `http://localhost:5173/main.js`).
    *   **In Production:** This will output the path to the versioned asset from your `dist` directory (e.g., `dist/assets/main.12345.js`).

3.  **Include Styles:**

    If your JavaScript entry point imports CSS, use the `styles()` method to get the path to the corresponding CSS file in production.

    ```php
    // In the <head> of your HTML
    <?php $cssFiles = $vite->styles('main.js'); ?>
    <?php foreach($cssFiles as $css): ?>
    
        <link rel="stylesheet" href="<?= $css ?>">
    
    <?php endforeach; ?>
    ```

4.  **React Fast Refresh (Development):**

    If you are using React, you can add the necessary script for Fast Refresh in your development environment.

    ```php
    // In your main layout/template file
    <?= $vite->addReactRefresh() ?>
    ```

    This will output the React Refresh script only when the Vite dev server is running.

## Configuration

You can customize the `ViteManager` by passing arguments to its constructor.

```php
use Millancore\Vite\ViteManager;

$vite = new ViteManager(
    dist: __DIR__ . '/public/build',
    customManifest: __DIR__ . '/public/build/.vite/manifest.json', // Optional: Direct path to the manifest
    host: '127.0.0.1',                    // Optional: Dev server host
    port: 3000                            // Optional: Dev server port
);
```

-   `dist`: The path to your build output directory (e.g., `public/dist`).
-   `customManifest`: A specific path to your `manifest.json` file. If not provided, the manager will look for it in `dist/.vite/manifest.json`.
-   `host`: The hostname of the Vite dev server. Defaults to `localhost`.
-   `port`: The port of the Vite dev server. Defaults to `5173`.

## Error Handling

The library throws exceptions for common errors:

-   `Millancore\Vite\ViteManifestNotFoundException`: Thrown if the `manifest.json` file cannot be found.
-   `Millancore\Vite\ViteException`: Thrown for other errors, such as invalid JSON in the manifest or if an asset is not found in the manifest.

It's recommended to wrap calls to the library in a `try...catch` block.

```php
try {
    $scriptUrl = $vite->get('main.js');
} catch (\Millancore\Vite\ViteException $e) {
    // Handle the error, e.g., log it or show a friendly message
    error_log($e->getMessage());
    $scriptUrl = ''; // Fallback
}
```