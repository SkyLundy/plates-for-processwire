### Asset Loader Extension

The asset loader extension provides tools to easily manage loading CSS, JS, and font assets. In the case of CSS and JS files,  cache parameter strings based on the last update time for files are automatically added when rendering to the page. It also provides tools for preloading assets as needed. With the asset loader extension your `<link>` and `<script>` tags are automatically created for you.

This extension provides the functionality of Plates' optional [Asset](https://platesphp.com/extensions/asset/) extension but adds folder behavior and markup generation.

This extension provides two ways to interact with your files:

**Using Folder Definitions**

For a native feel that matches Plates' own [Folders](https://platesphp.com/engine/folders/) feature, you may set up folder definitions within the Plates for ProcessWire module config. To do this, enable the Asset Loader extension, then specify folder names and associated directories under "Asset Loader - Folder Definitions". Folder names and their locations are entirely your choice and directories may be located anywhere relative to the root directory. Example:

```
css::/site/assets/bundle/styles
styles::/site/resources/css
js::/site/templates/javascript
scripts::/site/js
lib::/lib
fonts::/site/fonts
someCompletelyRandomName::/any/path/you/want
```

**Using Relative Paths**

If folder definitions feel like a little too much black magic or you just prefer using file paths, this extension will work using that method as well. All of the features will work exactly the same way and as long as the file exists, they'll also be given a cache busting parameter as well. There are no downsides to using file paths over folder definitions.

#### Linking Assets

Linking assets is easy. If you are using folder definitions, the extension will automatically output the correct HTML tag. If you're not using folder definitions, just use the appropriate function. If the file is found on the filesystem, a cache busting parameter will be added, otherwise it will be left off.

**Stylesheets**

```php
<!-- With folder definitions -->
<?=$this->linkAsset('css::styles.css')?>

<!-- Without folder definitions -->
<?=$this->linkCss('/path/to/your/styles.css')?>

<!-- Output -->
<link href="/path/to/your/styles.css?v=1734630086" rel="stylesheet">

<!-- You may also pass an arbitrary amount of attributes as a second array argument using either rendering method -->
<?=$this->linkAsset('css::styles.css', ['id' => 'some-id', 'data-some-attribute'])?>

<!-- Output -->
<link id="some-id" data-some-attribute href="/site/assets/bundle/styles/app.css?v=1734630086" rel="stylesheet">
```

**JavaScript**

```php
<!-- With folder definitions -->
<?=$this->linkAsset('js::script.js')?>

<!-- Without folder definitions -->
<?=$this->linkJs('/path/to/your/script.js')?>

<!-- Output: -->
<script src="/path/to/your/script.js?v=1734630080"></script>

<!-- Also takes optional second argument array of attributes -->
<?=$this->linkJs('/path/to/your/script.js', ['id' => 'some-id', 'data-some-attribute'])?>

<!-- Output -->
<script id="some-id" data-some-attribute src="/path/to/your/script.js?v=1734630080"></script>
```

#### Inlining Assets

You can also inline the contents of CSS or JS assets

**Stylesheets**

```php
<!-- With folder definitions -->
<?=$this->inlineAsset('css::styles.css')?>

<!-- Without folder definitions -->
<?=$this->inlineJs('/path/to/your/styles.css')?>

<!-- Output: -->
<style>
  body {
    font-family: 'Helvetica';
  }
</style>

<!-- Also takes optional second argument array of attributes, works with either method -->
<?=$this->inlineAsset('css::styles.css', ['id' => 'some-id', 'data-some-attribute'])?>

<!-- Output -->
<style id="some-id" data-some-attribute>
  body {
    font-family: 'Helvetica';
  }
</style>
```

**JavaScript**

```php
<!-- With folder definitions -->
<?=$this->inlineAsset('js::script.js')?>

<!-- Without folder definitions -->
<?=$this->inlineJs('/path/to/your/script.js')?>

<!-- Output: -->
<script>
  console.log('Hello');
</script>

<!-- Also takes optional second argument array of attributes, works with either method -->
<?=$this->inlineAsset('js::script.js', ['id' => 'some-id', 'data-some-attribute'])?>

<!-- Output -->
<script id="some-id" data-some-attribute>
  console.log('Hello');
</script>
```

#### Preloading Assets

You can also preload assets via `<link>` tags. Preloading assets works with CSS, JS, and font files. CSS and JS will have the correct cache busting parameter appended to the URL

```php
<!-- You may pass any type of file when using configured folders -->
<?=$this->preloadAsset('css::styles.css')?>
<?=$this->preloadAsset('js::script.js')?>
<?=$this->preloadAsset('fonts::your-font.woff')?>

<!-- Withoud configured folders, call the respective methods -->
<?=$this->preloadCss('/path/to/your/styles.css')?>
<?=$this->preloadJs('/path/to/your/script.js')?>
<?=$this->preloadFont('/path/to/your-font.woff')?>

<!-- Output respectively -->
<link rel="preload" href="/path/to/your/styles.css?v=1734630086" as="style">
<link rel="preload" href="/path/to/your/script.js?v=1734630080" as="script">
<link rel="preload" href="/path/to/your-font.woff" as="font" crossorigin>
```

#### Getting Asset Paths For Configured Folders

You can get the path of any asset either relative or absolute.

```php
// Returns /path/to/your/styles.css
<?=$this->getAssetPath('css::styles.css')?>

// Passing a boolean true as the second argument returns an absolute path
// Returns https://yourwebsite.com/path/to/your/styles.css
<?=$this->getAssetPath('css::styles.css', true)?>
```

#### Asset Loader Debug Mode

Enabling the asset loader extension will provide a method to enable a debug mode exclusively for this extension. This may be useful for troubleshooting during development, but is not recommended for use in production. Enabling debug mode will cause exceptions to be thrown if attemping to load/inline/preload a file that does not exist or attempt to use a configured folder that has not been set up on the module config page.
