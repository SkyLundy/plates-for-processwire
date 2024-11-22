<?php

declare(strict_types=1);

namespace ProcessWire;

use League\Plates\Engine;
use League\Plates\Template\Template;
use Plates\Extensions\{EmbedExtension, FunctionsExtension, WireExtension};

class Plates extends WireData implements Module, ConfigurableModule
{

    public readonly Engine $engine;

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
        $this->wire->set('plates', $this);

        $this->wire('classLoader')->addNamespace('Plates\Extensions', __DIR__ . '/Extensions');

        $this->engine = $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function ready()
    {
        $this->registerProcessWireObjects($this->engine);

        // Load extensions if configured in module
        $this->add_functions_extension && $this->engine->loadExtension(new FunctionsExtension());
        $this->add_embed_extension && $this->engine->loadExtension(new EmbedExtension());
        $this->add_wire_extension && $this->engine->loadExtension(new WireExtension());
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

        return new Engine($templatesDir);
    }

    /**
     * Loads the specified Template object to the $plate variable when needed outside of the current
     * template file scope
     * @param  Template|null $platesTemplate The template to assign to $plate
     * @return void
     */
    public function global(?Template $platesTemplate = null): void
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
     * Create inputfields for module configuration
     * @param  InputfieldWrapper $inputfields InputfieldWrapper for module config page
     * @return InputfieldWrapper
     */
    public function getModuleConfigInputfields(InputfieldWrapper $inputfields): InputfieldWrapper
    {
        $modules = $this->wire('modules');

        $fieldset = $modules->InputfieldFieldset;
        $fieldset->label = 'Plates for ProcessWire Extensions';
        $fieldset->description = 'Additional functions and features to enhance templates and workflows';
        $fieldset->collapsed = Inputfield::collapsedNever;

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_functions_extension',
          'label' => 'Add additional assistant methods to Plates?',
          'label2' => 'Add functions extension',
          'checked' => $this->add_functions_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_embed_extension',
          'label' => 'Add embed functionality to Plates?',
          'label2' => 'Add embed extension',
          'checked' => $this->add_embed_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_wire_extension',
          'label' => 'Add Wire utility functions to Plates?',
          'label2' => 'Add Wire extension',
          'checked' => $this->add_embed_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $inputfields->add($fieldset);

        // $inputfields->add([
        //   'type' => 'markup',
        //   'name' => 'functions_extension_example',
        //   'label' => 'Functions Extension',
        //   'value' => <<<EOT
        //     <p>Plates for ProcessWire can provide additional assistant functions to make working with data in your Plates template files easier and more efficient should you choose to include them. When rendering files using plates, in addition to having all ProcessWire objects available, additional functions are also made available.</p>
        //     <p>For more information about assistant function that can be included when Plates is loaded, refer to <code>/site/modules/Plates/Extensions/FunctionsExtension.php</code></p>
        //    EOT,
        // ]);

        return $inputfields;
    }
}
