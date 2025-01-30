<?php

/**
 * Provides methods to link assets in Plates templates
 *
 * Inspired by the plates-includer extension
 * https://github.com/odahcam/plates-includer/tree/master
 */

declare(strict_types=1);

namespace PlatesForProcessWire\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use LogicException;
use stdClass;

use function ProcessWire\wire;

class AssetLoaderExtension implements ExtensionInterface
{
    public $engine;

    /**
     * Parsed asset definitions from module config
     * @var array
     */
    private $folderDefinitions = [];

    public function __construct(
        ?string $assetConfigs,
        private bool $debugMode = false
    ) {
        is_string($assetConfigs) && $this->parseAssetConfigs($assetConfigs);
    }

    /**
     * {@inheritdoc}
     */
    public function register(Engine $engine)
    {
        $this->engine = $engine;

        $engine->registerFunction('getAssetPath', [$this, 'getAssetPath']);

        $engine->registerFunction('linkAsset', [$this, 'linkAsset']);
        $engine->registerFunction('linkAssetIf', [$this, 'linkAssetIf']);

        $engine->registerFunction('linkAssets', [$this, 'linkAssets']);
        $engine->registerFunction('linkAssetsIf', [$this, 'linkAssetsIf']);

        $engine->registerFunction('inlineAsset', [$this, 'inlineAsset']);
        $engine->registerFunction('inlineAssetIf', [$this, 'inlineAssetIf']);

        $engine->registerFunction('inlineAssets', [$this, 'inlineAssets']);
        $engine->registerFunction('inlineAssetsIf', [$this, 'inlineAssetsIf']);

        $engine->registerFunction('linkCss', [$this, 'linkCss']);
        $engine->registerFunction('linkCssIf', [$this, 'linkCssIf']);

        $engine->registerFunction('inlineCssIf', [$this, 'inlineCssIf']);

        $engine->registerFunction('linkJs', [$this, 'linkJs']);
        $engine->registerFunction('linkJsIf', [$this, 'linkJsIf']);

        $engine->registerFunction('inlineJs', [$this, 'inlineJs']);
        $engine->registerFunction('inlineJsIf', [$this, 'inlineJsIf']);

        $engine->registerFunction('preloadAsset', [$this, 'preloadAsset']);
        $engine->registerFunction('preloadAssetIf', [$this, 'preloadAssetIf']);

        $engine->registerFunction('preloadAssets', [$this, 'preloadAssets']);
        $engine->registerFunction('preloadAssetsIf', [$this, 'preloadAssetsIf']);

        $engine->registerFunction('preloadCss', [$this, 'preloadCss']);
        $engine->registerFunction('preloadCssIf', [$this, 'preloadCssIf']);

        $engine->registerFunction('preloadJs', [$this, 'preloadJs']);
        $engine->registerFunction('preloadJsIf', [$this, 'preloadJsIf']);

        $engine->registerFunction('preloadFont', [$this, 'preloadFont']);
        $engine->registerFunction('preloadFontIf', [$this, 'preloadFontIf']);
    }

    /**
     * Returns the asset path for a given folderfile string
     * @param  string       $folderFile Namespaced folder with filename
     * @param  bool         $absolute   Return as absolute path with domain
     * @return string|null
     */
    public function getAssetPath(string $folderFile, bool $absolute = false): ?string
    {
        $path = $this->parseFolderFile($folderFile)->filepath;

        if (!$absolute) {
            return $path;
        }

        return rtrim(wire('config')->urls->httpRoot, '/') . $path;
    }

    /**
     * Links an asset, automatically identifies type by file extension. Finds file according to
     * paths configured in module
     *
     * @param  string       $folderFile Folder and file, folder defined in module config
     * @param  array        $attributes Attributes added to link/script tag
     * @return string|null Null if file doesn't exist
     */
    public function linkAsset(string $folderFile, array $attributes = []): string
    {
        $parsedFolderFile = $this->parseFolderFile($folderFile);

        // No change to output but will trigger exception if extension debug is enabled
        $this->folderConfigured($parsedFolderFile->folder);

        return match ($parsedFolderFile->type) {
            'css' => $this->linkCss($parsedFolderFile->filepath, $attributes),
            'js' => $this->linkJs($parsedFolderFile->filepath, $attributes),
        };
    }

    /**
     * Links an asset if the first argument is truthy
     *
     * @see AssetLoaderExtension:linkAsset()
     *
     * @return string|null
     */
    public function linkAssetIf(
        mixed $conditional,
        string $folderFile,
        array $attributes = []
    ): ?string {
        return !!$conditional ? $this->linkAsset($folderFile, $attributes) : null;
    }

    /**
     * Convenience method to link multiple assets of any type.
     * Does not accept attributes for tags
     *
     * @param  array  $folderFiles Array of folderfile strings
     * @return string
     */
    public function linkAssets(array $folderFiles): string
    {
        $markups = array_map(fn ($folderFile) => $this->linkAsset($folderFile), $folderFiles);

        return implode("\n", $markups);
    }

    /**
     * Links an array of assets if the conditional argument is truthy
     *
     * @see AssetLoaderExtension::linkAssets()
     *
     * @return string|null
     */
    public function linkAssetsIf(mixed $conditional, array $folderFiles): ?string
    {
        return !!$conditional ? $this->linkAssets($folderFiles) : null;
    }

    /**
     * Loads contents of a given file and returns the contents with the appropriate wrapping HTML
     * element
     *
     * @param  string $folderFile Configured folder/filepath present in module config
     * @param  array  $attributes Attributes added to the link/script tag
     * @return string
     */
    public function inlineAsset(string $folderFile, array $attributes = []): string
    {
        $parsedFolderFile = $this->parseFolderFile($folderFile);

        return match ($parsedFolderFile->type) {
            'css' => $this->inlineCss($parsedFolderFile->filepath, $attributes),
            'js' => $this->inlineJs($parsedFolderFile->filepath, $attributes),
        };
    }

    /**
     * Links an array of assets if the conditional argument is truthy
     *
     * @see AssetLoaderExtension::inlineAsset()
     *
     * @param  mixed  $conditional Value evaluated for truthy/falsey value
     * @param  string $folderFile  Configured folder/filepath present in module config
     * @param  array  $attributes  Attributes added to the link/script tag
     * @return string|null
     */
    public function inlineAssetIf(
        mixed $conditional,
        string $folderFile,
        array $attributes = [],
    ): ?string {
        return !!$conditional ? $this->inlineAsset($folderFile, $attributes) : null;
    }

    /**
     * Convenience method to inline multiple assets of any type.
     * Does not accept attributes for tags
     *
     * @param  array        $folderFiles Array of folderfile strings
     * @return string|null
     */
    public function inlineAssets(array $folderFiles): ?string
    {
        $markups = array_map(fn ($folderFile) => $this->inlineAsset($folderFile), $folderFiles);

        return implode("\n", $markups);
    }

    /**
     * Convenience method to inline multiple assets of any type.
     * Does not accept attributes for tags
     *
     * @param  array  $folderFiles Array of folderfile strings
     * @return string
     */
    public function inlineAssetsIf(mixed $conditional, array $folderFiles): ?string
    {
        return !!$conditional ? $this->inlineAssets($folderFiles) : null;
    }


    /**
     * Creates a <link> tag for a CSS file
     *
     * @param  string  $filepath    Filepath relative to root
     * @param  array   $attributes  Additional attributes for the <link> tag
     * @return string
     */
    public function linkCss(string $filepath, array $attributes = []): string
    {
        // No change to output but will trigger exception if extension debug enabled
        $this->fileExists($filepath);

        $attributes = $this->createAttributeString([
            ...$attributes,
            'href' => $this->addCacheBustParameter($filepath),
            'rel' => 'stylesheet',
        ]);

        return "<link {$attributes} />";
    }

    /**
     * Convenience method to link a CSS file conditionally.
     *
     * @param  string  $filepath Filepath relative to roots
     * @return string|null
     */
    public function linkCssIf(
        mixed $conditional,
        string $filepath,
        array $attributes = [],
    ): ?string {
        return !!$conditional ? $this->linkCss($filepath, $attributes) : null;
    }

    /**
     * Inlines the content of a CSS file within a <style> tag
     *
     * @param  string  $filepath  File to inline
     * @return string
     */
    public function inlineCss(string $filepath, array $attributes = []): string
    {
        $attributes = $this->createAttributeString($attributes);

        $attributes && $attributes = " {$attributes}";

        $css = $this->getFileContents($filepath);

        $markup =<<<EOT
        <style{$attributes}>
          {$css}
        </style>
        EOT;

        return $markup;
    }

    /**
     * Convenience method to inline a CSS file conditionally.
     *
     * @param  string  $filepath Filepath relative to roots
     * @return string|null
     */
    public function inlineCssIf(
        mixed $conditional,
        string $filepath,
        array $attributes = [],
    ): ?string {
        return !!$conditional ? $this->inlineCss($filepath, $attributes) : null;
    }

    /**
     * Create a <script> tag linking a JS file
     *
     * @param  string       $file       File with relative path to root
     * @param  array        $attributes Attributes added to the <script> tag
     * @return string
     */
    public function linkJs(string $filepath, array $attributes = []): string
    {
        // No change to output but will trigger exception if extension debug enabled
        $this->fileExists($filepath);

        $attributes = $this->createAttributeString([
            ...$attributes,
            'src' => $this->addCacheBustParameter($filepath),
        ]);

        return "<script {$attributes}></script>";
    }

    /**
     * Convenience method to link a JS file conditionally.
     *
     * @param  string  $filepath Filepath relative to roots
     * @return string|null
     */
    public function linkJsIf(
        mixed $conditional,
        string $filepath,
        array $attributes = [],
    ): ?string {
        return !!$conditional ? $this->linkJs($filepath, $attributes) : null;
    }

    /**
     * Inlines the contents of a JS file
     *
     * @param  string  $filepath    File inline contents of
     * @param  array   $attributes  Attributes added to the <script> tag
     * @return string
     */
    public function inlineJs(string $filepath, array $attributes = []): string
    {
        $attributes = $this->createAttributeString($attributes);

        $attributes && $attributes = " {$attributes}";

        $js = $this->getFileContents($filepath);

        $markup =<<<EOT
        <script{$attributes}>
          {$js}
        </script>
        EOT;

        return $markup;
    }

    /**
     * Convenience method to inline a JS file conditionally.
     *
     * @param  string  $filepath Filepath relative to roots
     * @return string|null
     */
    public function inlineJsIf(
        mixed $conditional,
        string $filepath,
        array $attributes = [],
    ): ?string {
        return !!$conditional ? $this->inlineJs($filepath, $attributes) : null;
    }


    /**
     * Preload a CSS, JS, or font files
     *
     * @param  string $folderFile  Configured folder or filepath
     * @return string
     */
    public function preloadAsset(string $folderFile): string
    {
        $parsedFolderFile = $this->parseFolderFile($folderFile);

        // No change to output but will trigger exception if extension debug is enabled
        $this->folderConfigured($parsedFolderFile->folder);

        return match ($parsedFolderFile->type) {
            'css' => $this->preloadCss($parsedFolderFile->filepath),
            'js' => $this->preloadJs($parsedFolderFile->filepath),
            'font' => $this->preloadFont($parsedFolderFile->filepath),
        };
    }

    /**
     * Preload a CSS, JS, or font file
     *
     * @param  mixed  $conditional Value evaluated for truthey/falsey
     * @param  string $folderFile  Configured folder or filepath
     * @return string|null
     */
    public function preloadAssetIf(mixed $conditional, string $folderFile): ?string
    {
        return !!$conditional ?  $this->preloadAsset($folderFile) : null;
    }

    /**
     * Preload multiple assets in one function call
     * @param  array  $folderFiles Files with configured folder prefix
     * @return string
     */
    public function preloadAssets(array $folderFiles): string
    {
        $markup = array_map(fn ($folderFile) => $this->preloadAsset($folderFile), $folderFiles);

        return implode("\n", $markup);
    }

    /**
     * Preload multiple assets in one function call conditionally based on truthy/falsey value of
     * first passed argument
     * @param  mixed  $conditional Value evaluated for truthy/falsey value
     * @param  array  $folderFiles Files with configured folder prefix
     * @return string|null
     */
    public function preloadAssetsIf(mixed $conditional, array $folderFiles): ?string
    {
        return !!$conditional ? $this->preloadAssets($folderFiles) : null;
    }

    /**
     * Preload a specified CSS file
     * @param  string $filepath Filepath of asset to preload
     * @return string
     */
    public function preloadCss(string $filepath): string
    {
        $attributes = $this->createAttributeString([
            'rel' => 'preload',
            'href' => $this->addCacheBustParameter($filepath),
            'as' => 'style',
        ]);

        return "<link {$attributes} />";
    }

    /**
     * Preload a CSS file conditionally
     * @param  mixed  $conditional Value evaluated for truthy/falsey value
     * @param  string $filepath    Path to file
     * @return string|null
     */
    public function preloadCssIf(mixed $conditional, string $filepath): ?string
    {
        return !!$conditional ? $this->preloadCss($filepath) : null;
    }

    /**
     * Preload a specified JS file
     * @param  string $filepath Filepath of asset to preload
     * @return string
     */
    public function preloadJs(string $filepath): string
    {
        $attributes = $this->createAttributeString([
            'rel' => 'preload',
            'href' => $this->addCacheBustParameter($filepath),
            'as' => 'script',
        ]);

        return "<link {$attributes} />";
    }

    /**
     * Preload a JS file conditionally
     * @param  mixed  $conditional Value evaluated for truthy/falsey value
     * @param  string $filepath    Path to file
     * @return string|null
     */
    public function preloadJsIf(mixed $conditional, string $filepath): ?string
    {
        return !!$conditional ? $this->preloadJs($filepath) : null;
    }

    /**
     * Preload a specified font file
     * @param  string $filepath File of asset to preload
     * @return string
     */
    public function preloadFont(string $filepath): string
    {
        $attributes = $this->createAttributeString([
            'rel' => 'preload',
            'href' => $filepath,
            'as' => 'font',
            'crossorigin',
        ]);

        return "<link {$attributes} />";
    }

    /**
     * Preload a font file conditionally
     * @param  mixed  $conditional Value evaluated for truthy/falsey value
     * @param  string $filepath    Path to file
     * @return string|null
     */
    public function preloadFontIf(mixed $conditional, string $filepath): ?string
    {
        return !!$conditional ? $this->preloadFont($filepath) : null;
    }

    /**
     * Adds a cache busting GET string to a filename based on the last updated datetime
     * @param string $file Name of file, must exist on the file system to get a cache busting string
     * @return string
     */
    public function addCacheBustParameter(string $filepath): string
    {
        $pathinfo = pathinfo($filepath);

        $absolutePath = $this->getAbsolutePath($filepath);

        $basename = $pathinfo['basename'] ?? false;

        if (!$absolutePath || !$basename) {
            return $filepath;
        }

        $updatedAt = filemtime($absolutePath);

        $bustedAsset = "{$basename}?v={$updatedAt}";

        return str_replace($basename, $bustedAsset, $filepath);
    }

    /**
     * Private processing methods
     */

    /**
     * Parses module path config values. Splits to array of ['namespace' => 'path']
     *
     * @param  string $assetConfigs Module config string
     * @return void
     * @throws LogicException
     */
    private function parseAssetConfigs(string $assetConfigs): void
    {
        if (!$assetConfigs) {
            return;
        }

        $assetConfigs = explode("\n", $assetConfigs);

        $definitions = array_map(function($config) {
            [$folder, $path] = explode('::', $config);

            // Ensure format path
            $path = rtrim($path, '/');
            $path = ltrim($path, '/');
            $path = "/{$path}";

            return [$folder => $path];
        }, $assetConfigs);

        $this->folderDefinitions = array_merge(...$definitions);
    }

    /**
     * Creates an attribute string for use with markup tags
     * @param  array  $attributes Array of attributes as key or key/value
     * @return string
     */
    private function createAttributeString(array $attributes): string
    {
        $attributes = array_filter($attributes, fn ($value) => !is_null($value));

        array_walk($attributes, function(&$value, $attribute) {
            is_bool($value) && $value = filter_var($value, FILTER_VALIDATE_BOOL) ? 'true' : 'false';

            if (is_int($attribute)) {
                $value = $value;

                return;
            }

            $value = trim("{$attribute}=\"{$value}\"");
        });

        $attributes = array_values($attributes);

        if (!$attributes) {
            return '';
        }

        $attributes = implode(' ', $attributes);

        return " {$attributes}";
    }

    /**
     * Gets contents of a specified file
     *
     * @param  string  $file  Relative filepath of file to get contents of
     * @return string|null
     */
    private function getFileContents(string $filepath): ?string
    {
        if (!$this->fileExists($filepath)) {
            return null;
        }

        return wire('files')->fileGetContents(
            $this->getAbsolutePath($filepath)
        );
    }

    /**
     * Parses a folder/file argument such as css::app.css
     *
     * @param  string $folderFile argument passed to asset method
     * @return stdClass
     */
    private function parseFolderFile(string $folderFile): stdClass
    {
        $folderFileComponents = explode('::', $folderFile);

        if (count($folderFileComponents) !== 2) {
            throw new LogicException(
                "'{$folderFile}' cannot be parsed as a configured folder asset"
            );
        }

        [$folder, $file] = $folderFileComponents;

        $filepath = "{$this->folderDefinitions[$folder]}/{$file}";

        $pathinfo = pathinfo($filepath);

        $ext = strtolower($pathinfo['extension']);

        $type = match (true) {
            $ext === 'css' => 'css',
            $ext === 'js' => 'js',
            in_array($ext, ['woff', 'woff2', 'ttf', 'otf', 'svg', 'eot']) => 'font',
            default => null,
        };

        return (object) [
            'folder' => $folder,
            'filepath' => $filepath,
            'fileExtension' => $pathinfo['extension'],
            'type' => $type,
        ];
    }

    /**
     * Checks that the specified file exists
     *
     * @param  string $file File to check existence of
     * @return bool
     * @throws LogicException
     */
    private function fileExists(string $filepath): bool
    {
        $absolutePath = $this->getAbsolutePath($filepath);

        $fileExists = wire('files')->exists($absolutePath);

        if (!$fileExists && $this->debugMode) {
            throw new LogicException("The file '{$filepath}' does not exist");
        }

        return $fileExists;
    }

    /**
     * Checks that a specified folder is configured
     * @param  string  $folderName  Name of folder expected in module/extension config
     * @return bool
     */
    private function folderConfigured(string $folderName): bool
    {
        $configuredFolders = array_keys($this->folderDefinitions);

        $folderConfigured = in_array($folderName, $configuredFolders);

        if (!$folderConfigured && $this->debugMode) {
            throw new LogicException(
                "The folder '{$folderName}' is missing or misconfigured. Review the extension settings in the Plates for ProcessWire module config"
            );
        }

        return $folderConfigured;
    }


    /**
     * Returns the absolute filesystem path for a given file
     *
     * Format:
     * /absolute/filesystem/path/to/file.extension
     *
     * @param  string $file Path to file
     * @return string
     */
    private function getAbsolutePath(string $filepath): string
    {
        $pathinfo = pathinfo($filepath);

        $root = wire('config')->paths->root;

        $dirname = ltrim($pathinfo['dirname'], '/');
        $dirname = rtrim($dirname, '/');

        $absolutePath = "{$root}{$dirname}/{$pathinfo['basename']}";

        return $absolutePath;
    }
}
