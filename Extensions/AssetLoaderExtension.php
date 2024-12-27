<?php

/**
 * Provides methods to link assets in Plates templates
 *
 * Inspired by the plates-includer extension
 * https://github.com/odahcam/plates-includer/tree/master
 */

declare(strict_types=1);

namespace Plates\Extensions;

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

        $engine->registerFunction('linkAsset', [$this, 'linkAsset']);
        $engine->registerFunction('linkAssets', [$this, 'linkAssets']);

        $engine->registerFunction('inlineAsset', [$this, 'inlineAsset']);
        $engine->registerFunction('inlineAssets', [$this, 'inlineAssets']);

        $engine->registerFunction('linkCss', [$this, 'linkCss']);
        $engine->registerFunction('inlineCss', [$this, 'inlineCss']);

        $engine->registerFunction('linkJs', [$this, 'linkJs']);
        $engine->registerFunction('inlineJs', [$this, 'inlineJs']);

        $engine->registerFunction('preloadAsset', [$this, 'preloadAsset']);
        $engine->registerFunction('preloadAssets', [$this, 'preloadAssets']);

        $engine->registerFunction('preloadCss', [$this, 'preloadCss']);
        $engine->registerFunction('preloadJs', [$this, 'preloadJs']);
        $engine->registerFunction('preloadFont', [$this, 'preloadFont']);
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
     * Preload multiple assets in one functio call
     * @param  array  $folderFiles Files with configured folder prefix
     * @return string
     */
    public function preloadAssets(array $folderFiles): string
    {
        $markup = array_map(fn ($folderFile) => $this->preloadAsset($folderFile), $folderFiles);

        return implode("\n", $markup);
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
        $attributeStrings = [];

        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $attributeStrings[] = trim($value);

                continue;
            }

            $key = trim($key);
            $value = trim($value);

            $attributeStrings[] = "{$key}=\"{$value}\"";
        }

        return implode(' ', $attributeStrings);
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
