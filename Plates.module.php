<?php

declare(strict_types=1);

namespace ProcessWire;

use League\Plates\Engine;
use League\Plates\Template\Template;
use Plates\Extensions\{
    AssetLoaderExtension,
    CaptureExtension,
    ConditionalsExtension,
    FunctionsExtension,
    WireExtension
};

class Plates extends WireData implements Module, ConfigurableModule
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
        // CHECK IF Plates COMPOSER MODULE IS INSTALLED

        $this->wire->set('plates', $this);

        $this->wire('classLoader')->addNamespace('Plates\Extensions', __DIR__ . '/Extensions');

        $this->templates = $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function ready()
    {
        $this->registerProcessWireObjects($this->templates);
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

        if ($this->add_capture_extension) {
            $engine->loadExtension(new CaptureExtension());
        }

        if ($this->add_wire_extension) {
            $engine->loadExtension(new WireExtension());
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
          'value' => $this->plates_file_extension ?: '',
          'collapsed' => Inputfield::collapsedNever,
          'themeInputWidth' => 'l',
          'columnWidth' => 100,
        ]);

        $fieldset = $modules->InputfieldFieldset;
        $fieldset->label = 'Plates for ProcessWire Extensions';
        $fieldset->description = 'Additional functions and features to enhance templates and workflows';
        $fieldset->notes = "Reference this module's README.md document for documentation.";
        $fieldset->collapsed = Inputfield::collapsedNever;

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_functions_extension',
          'label' => 'Functions Extension',
          'label2' => 'Add functions extension',
          'description' => 'Convenient and powerful functions to work with values in your Plates templates.',
          'notes' => 'Requires PHP mbstring extension',
          'checked' => $this->add_functions_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 50,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_conditionals_extension',
          'label' => 'Conditionals Extension',
          'label2' => 'Add conditionals extension',
          'description' => 'Functions that assist with conditional rendering and working with markup.',
          'notes' => 'Requires PHP mbstring extension',
          'checked' => $this->add_functions_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 50,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_capture_extension',
          'label' => 'Capture Extension',
          'label2' => 'Add capture extension',
          'description' => 'Extends reusability with the ability to render Plates templates with blocks.',
          'checked' => $this->add_capture_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 50,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_wire_extension',
          'label' => 'Wire Objects Extension',
          'label2' => 'Add Wire extension',
          'description' => 'Easily access useful ProcessWire utilties and object creators',
          'checked' => $this->add_wire_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 50,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_asset_loader_extension',
          'label' => 'Asset Loader Extension',
          'label2' => 'Add asset loader extension',
          'description' => 'Load assets with ease using convenient functions. Optionally add cache busting parameters.',
          'checked' => $this->add_asset_loader_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $fieldset->add([
          'type' => 'textarea',
          'name' => 'asset_loader_definitions',
          'label' => 'Asset Loader - Folder Definitions',
          'description' => 'Define the locations of assets using arbitrarily named folders.',
          'value' => $this->asset_loader_definitions ?? '',
          'placeholder' => "css::/path/to/assets/here\njs::/path/to/assets/here\nlib::/path/to/assets/here",
          'notes' => 'Format: name::/path/from/root/direcory',
          'collapsed' => Inputfield::collapsedNever,
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
          'checked' => $this->asset_loader_debug_mode ?? '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $inputfields->add($fieldset);

        // $fieldset = $modules->InputfieldFieldset;
        // $fieldset->label = 'Asset Loader Extension Configuration';
        // $fieldset->description = 'Configure options for loading assets';
        // $fieldset->showIf = 'add_asset_loader_extension=1';
        // $fieldset->collapsed = Inputfield::collapsedNever;

        // $fieldset->add([
        //   'type' => 'textarea',
        //   'name' => 'asset_loader_definitions',
        //   'label' => 'File Definitions',
        //   'description' => 'Define the locations of assets using arbitrarily named folders.',
        //   'value' => $this->asset_loader_definitions ?? '',
        //   'required' => true,
        //   'requiredIf' => 'add_asset_loader_extension=1',
        //   'placeholder' => "css::/path/to/assets/here\njs::/path/to/assets/here\nlib::/path/to/assets/here",
        //   'notes' => 'Format: name::/path/from/root/direcory',
        //   'collapsed' => Inputfield::collapsedNever,
        //   'themeBorder' => 'hide',
        //   'columnWidth' => 100 / 3,
        // ]);

        // $fieldset->add([
        //   'type' => 'checkbox',
        //   'name' => 'asset_loader_debug_mode',
        //   'label' => 'Debug Mode',
        //   'label2' => 'Enable debug mode',
        //   'description' => 'With debug mode enabled, exceptions will be thrown for unexpected filetypes and files that cannot be found.',
        //   'notes' => 'Not recommended for use in production',
        //   'checked' => $this->asset_loader_debug_mode ?? '',
        //   'collapsed' => Inputfield::collapsedNever,
        //   'themeBorder' => 'hide',
        //   'columnWidth' => 100 / 3,
        // ]);

        // $inputfields->add($fieldset);

        return $inputfields;
    }
}
