<?php

declare(strict_types=1);

namespace ProcessWire;

use League\Plates\Engine;
use League\Plates\Template\Template;
use League\Plates\Extension\{Asset, URI};
use PlatesForProcessWire\Extensions\{
    AssetLoaderExtension,
    EmbedExtension,
    ConditionalsExtension,
    FunctionsExtension,
    SanitizerExtension,
    WireExtension
};

class PlatesForProcessWire extends WireData implements Module, ConfigurableModule
{
    public readonly Engine $templates;

    private const DEFAULT_FILE_EXTENSION = 'plates.php';

    /**
     * {@inheritdoc}
     */
    public static function getModuleInfo()
    {
        return [
            'title' => __('Plates for ProcessWire', __FILE__),
            'summary' => __('Provides template rendering via Plates by The League of Extraordinary Packages.', __FILE__),
            'author' => 'Firewire',
            'href' => '',
            'version' => '001',
            'autoload' => true,
            'singular' => true,
            'requires'  => [
                'ProcessWire>=300',
                'PHP>=8.2',
            ],
            'installs' => [],
            'icon' => 'code',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // Check if Plates is already loaded from another source
        if (!class_exists(Engine::class)) {
            require_once "{$this->wire->config->paths->$this}vendor/autoload.php";
        }

        $this->wire->set('plates', $this);

        $this->wire('classLoader')->addNamespace('PlatesForProcessWire\Extensions', __DIR__ . '/Extensions');

        $this->templates = $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function ready()
    {
        $this->registerProcessWireObjects($this->templates);
        $this->loadOptionalPlatesExtensions($this->templates);
        $this->loadModuleProvidedExtensions($this->templates);
    }

    /**
     * Create the Plates object
     * @param  string|null $templatesDirectory Optional templates directory to specify if other
     *                                         direcory preferred.
     * @return Engine
     */
    public function ___initialize(?string $templatesDir = null): Engine
    {
        $templatesDir =  rtrim($templatesDir ?? $this->wire('config')->paths->templates, '/');

        $fileExtension = ltrim($this->plates_file_extension ?: self::DEFAULT_FILE_EXTENSION, '.');

        return new Engine($templatesDir, $fileExtension);
    }

    /**
     * Loads the specified Template object to the $plate variable when needed outside of the current
     * template file scope
     * @param  Template|null $platesTemplate The template to assign to $plate
     * @return void
     */
    public function exposeTemplate(?Template $platesTemplate = null): void
    {
        $this->wire->set('plate', $platesTemplate);
    }

    /**
     * Injects all ProcessWire object variables for use in Plates files at runtime
     * @param  Engine $engine Plates Engine object
     */
    private function registerProcessWireObjects(Engine $engine): void
    {
        foreach ($this->wire('all')->getArray() as $name => $object) {
            $engine->addData([$name => $object]);
        }
    }

    /**
     * Loads any optional Plates extensions provided with this module if added via the module config
     * pate
     * @return void
     */
    private function loadModuleProvidedExtensions(Engine $engine): void
    {
        if ($this->add_functions_extension) {
            $engine->loadExtension(new FunctionsExtension());
        }

        if ($this->add_embed_extension) {
            $engine->loadExtension(new EmbedExtension());
        }

        if ($this->add_wire_extension) {
            $engine->loadExtension(new WireExtension());
        }

        if ($this->add_sanitizer_extension) {
            $engine->loadExtension(new SanitizerExtension());
        }

        if ($this->add_conditionals_extension) {
            $engine->loadExtension(new ConditionalsExtension());
        }

        if ($this->add_asset_loader_extension) {
            $engine->loadExtension(
                new AssetLoaderExtension(
                    $this->asset_loader_definitions,
                    !!$this->asset_loader_debug_mode,
                )
            );
        }
    }

    /**
     * Load optional Extensions that are provided by Plates
     * @param  Engine $engine
     * @return void
     */
    private function loadOptionalPlatesExtensions(Engine $engine): void
    {
        if ($this->add_plates_asset_extension) {
            $path = ltrim($this->plates_asset_extension_path, '/');

            $engine->loadExtension(
                new Asset(wire('config')->paths->root . $path, $this->plates_asset_caching_method === 'file')
            );
        }

        if ($this->add_plates_uri_extension) {
            $engine->loadExtension(new URI(wire('page')->path));
        }
    }

    /**
     * Create inputfields for module configuration
     * @param  InputfieldWrapper $inputfields InputfieldWrapper for module config page
     * @return InputfieldWrapper
     */
    public function getModuleConfigInputfields(InputfieldWrapper $inputfields): InputfieldWrapper
    {
        $modules = $this->wire('modules');

        $inputfields->add([
          'type' => 'text',
          'name' => 'plates_file_extension',
          'label' => 'Template File Extension',
          'description' => "The file extension that you would like to use for Plates template files. By default Plates will look for and recognize files with the extension '.plates.php', e.g 'yourfile.plates.php'",
          'placeholder' => 'plates.php',
          'value' => $this->plates_file_extension,
          'collapsed' => Inputfield::collapsedNever,
          'themeInputWidth' => 'l',
          'columnWidth' => 100,
        ]);

        $fieldset = $modules->InputfieldFieldset;
        $fieldset->label = 'Plates Extensions';
        $fieldset->description = 'Optionally enable official extensions provided by Plates';
        $fieldset->notes = "Reference the Plates documentation at https://platesphp.com for more information";
        $fieldset->collapsed = Inputfield::collapsedNever;

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_plates_uri_extension',
          'label' => 'Plates URI Extension',
          'label2' => 'Add Plates URI extension',
          'description' => 'Designed to make URI checks within templates easier.',
          'notes' => '[Documentation](https://platesphp.com/extensions/uri/)',
          'checked' => $this->add_plates_uri_extension,
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_plates_asset_extension',
          'label' => 'Plates Asset Extension',
          'label2' => 'Add Plates Asset extension',
          'description' => 'Quickly create “cache busted” asset URLs in your templates. For more features, review the Asset Loader custom extension below.',
          'notes' => '[Documentation](https://platesphp.com/extensions/asset/)',
          'checked' => $this->add_plates_asset_extension,
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 34,
        ]);

        $fieldset->add([
          'type' => 'text',
          'name' => 'plates_asset_extension_path',
          'label' => 'Asset Public Path',
          'description' => 'The location of public assets.',
          'placeholder' => '/path/to/assets/dir/from/root/',
          'value' => $this->plates_asset_extension_path,
          'collapsed' => Inputfield::collapsedNever,
          'required' => true,
          'requireIf' => 'add_plates_asset_extension=1',
          'showIf' => 'add_plates_asset_extension=1',
          'themeBorder' => 'hide',
          'columnWidth' => 33,
        ]);

        $fieldset->add([
          'type' => 'select',
          'name' => 'plates_asset_caching_method',
          'label' => 'Asset Caching Method',
          'description' => 'Method of setting file caching values.',
          'options' => [
            'query' => "Query String - file.css?v=1373577602",
            'file' => "Filename - file.1373577602.css"
          ],
          'value' => $this->plates_asset_caching_method ?: 'query',
          'collapsed' => Inputfield::collapsedNever,
          'required' => true,
          'requireIf' => 'add_plates_asset_extension=1',
          'showIf' => 'add_plates_asset_extension=1',
          'notes' => 'Filename requires Apache/Nginx configuration. Review the extension documentation for details.',
          'themeBorder' => 'hide',
          'columnWidth' => 33,
        ]);

        $inputfields->add($fieldset);

        $fieldset = $modules->InputfieldFieldset;
        $fieldset->label = 'Plates for ProcessWire Extensions';
        $fieldset->description = "Additional custom functions and features to enhance templates and workflows. Documentation can be viewed below,";
        $fieldset->collapsed = Inputfield::collapsedNever;

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_functions_extension',
          'label' => 'Functions Extension',
          'label2' => 'Add functions extension',
          'description' => 'Convenient and powerful functions to work with values in your Plates templates.',
          'checked' => $this->add_functions_extension,
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_conditionals_extension',
          'label' => 'Conditionals Extension',
          'label2' => 'Add conditionals extension',
          'description' => 'Functions that assist with conditional markup rendering.',
          'checked' => $this->add_functions_extension,
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_embed_extension',
          'label' => 'Embed Extension',
          'label2' => 'Add embed extension',
          'description' => "Embed templates with blocks and capture markup to variables for output.",
          'checked' => $this->add_embed_extension,
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_wire_extension',
          'label' => 'Wire Objects Extension',
          'label2' => 'Add Wire extension',
          'description' => 'Easily access useful ProcessWire utilties and object creators',
          'checked' => $this->add_wire_extension,
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_sanitizer_extension',
          'label' => 'Sanitizer Extension',
          'label2' => 'Add Sanitizer extension',
          'description' => 'Wrapper for accessing ProcessWire sanitizer functions. Functions that accept one argument will be batchable',
          'checked' => $this->add_sanitizer_extension,
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_asset_loader_extension',
          'label' => 'Asset Loader Extension',
          'label2' => 'Add asset loader extension',
          'description' => 'Link and preload assets with ease using convenient functions and automatic file caching parameters.',
          'checked' => $this->add_asset_loader_extension,
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $fieldset->add([
          'type' => 'textarea',
          'name' => 'asset_loader_definitions',
          'label' => 'Asset Loader - Folder Definitions',
          'description' => 'Define the locations of assets using arbitrarily named folders.',
          'value' => $this->asset_loader_definitions,
          'placeholder' => "css::/path/to/assets/here\njs::/path/to/assets/here\nlib::/path/to/assets/here",
          'notes' => 'Format: name::/path/from/root/direcory',
          'collapsed' => Inputfield::collapsedNever,
          'showIf' => 'add_asset_loader_extension=1',
          'requireIf' => 'add_asset_loader_extension=1',
          'required' => true,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'asset_loader_debug_mode',
          'label' => 'Asset Loader - Debug Mode',
          'label2' => 'Enable debug mode',
          'description' => 'With debug mode enabled, exceptions will be thrown for unexpected filetypes and files that cannot be found.',
          'notes' => 'Not recommended for use in production',
          'checked' => $this->asset_loader_debug_mode,
          'collapsed' => Inputfield::collapsedNever,
          'showIf' => 'add_asset_loader_extension=1',
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $inputfields->add($fieldset);

        if (!wire('modules')->isInstalled('TextformatterMarkdownExtra')) {
            return $inputfields;
        }

        $textFormatterMarkdown = wire('modules')->get('TextFormatterMarkdownExtra');
        $docsPath = wire('config')->paths->$this . 'Extensions/Documentation/';

        $getMarkup = function(string $file) use ($docsPath, $textFormatterMarkdown) {
            return $textFormatterMarkdown->markdown(
                wire('files')->fileGetContents("{$docsPath}{$file}")
            );
        };


        $extensionsDocumentationFile = wire('config')->urls->$this . 'Extensions/Documentation';

        $fieldset = $modules->InputfieldFieldset;
        $fieldset->label = 'Plates for ProcessWire Extensions Documentation';
        $fieldset->description = "This information is also available via individual markdown files located in [{$extensionsDocumentationFile}]({$extensionsDocumentationFile})";
        $fieldset->collapsed = Inputfield::collapsedNever;

        // Conditionals Extension
        $fieldset->add([
          'type' => 'InputfieldMarkup',
          'label' => __('Conditionals Extension'),
          'value' => $getMarkup('ConditionalsExtension.md'),
          'collapsed' => Inputfield::collapsedYes,
          'themeOffset' => 's',
        ]);

        // Functions Extension
        $fieldset->add([
          'type' => 'InputfieldMarkup',
          'label' => __('Functions Extension'),
          'value' => $getMarkup('FunctionsExtension.md'),
          'collapsed' => Inputfield::collapsedYes,
          'themeOffset' => 's',
        ]);

        // Asset Loader Extension
        $fieldset->add([
          'type' => 'InputfieldMarkup',
          'label' => __('Asset Loader Extension'),
          'value' => $getMarkup('AssetLoaderExtension.md'),
          'collapsed' => Inputfield::collapsedYes,
          'themeOffset' => 's',
        ]);

        // Sanitizer Extension
        $fieldset->add([
          'type' => 'InputfieldMarkup',
          'label' => __('Sanitizer Extension'),
          'value' => $getMarkup('SanitizerExtension.md'),
          'collapsed' => Inputfield::collapsedYes,
          'themeOffset' => 's',
        ]);

        // Embed Extension
        $fieldset->add([
          'type' => 'InputfieldMarkup',
          'label' => __('Embed Extension'),
          'value' => $getMarkup('EmbedExtension.md'),
          'collapsed' => Inputfield::collapsedYes,
          'themeOffset' => 's',
        ]);

        // Wire Extension
        $fieldset->add([
          'type' => 'InputfieldMarkup',
          'label' => __('Wire Extension'),
          'value' => $getMarkup('WireExtension.md'),
          'collapsed' => Inputfield::collapsedYes,
          'themeOffset' => 's',
        ]);

        $inputfields->add($fieldset);

        return $inputfields;
    }
}
