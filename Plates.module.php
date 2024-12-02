<?php

declare(strict_types=1);

namespace ProcessWire;

use League\Plates\Engine;
use League\Plates\Template\Template;
use Plates\Extensions\{CaptureExtension, FunctionsExtension, WireExtension};

class Plates extends WireData implements Module, ConfigurableModule
{
    public readonly Engine $templates;

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

        $this->templates = $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function ready()
    {
        $this->registerProcessWireObjects($this->templates);

        // Load extensions if configured in module
        $this->add_functions_extension && $this->templates->loadExtension(new FunctionsExtension());
        $this->add_capture_extension && $this->templates->loadExtension(new CaptureExtension());
        $this->add_wire_extension && $this->templates->loadExtension(new WireExtension());
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
          'notes' => 'Requires PHP mbstring extension',
          'checked' => $this->add_functions_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_capture_extension',
          'label' => 'Add capture functionality to Plates?',
          'label2' => 'Add capture extension',
          'checked' => $this->add_capture_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $fieldset->add([
          'type' => 'checkbox',
          'name' => 'add_wire_extension',
          'label' => 'Add Wire utility functions to Plates?',
          'label2' => 'Add Wire extension',
          'checked' => $this->add_wire_extension ? 'checked' : '',
          'collapsed' => Inputfield::collapsedNever,
          'themeBorder' => 'hide',
          'columnWidth' => 100 / 3,
        ]);

        $inputfields->add($fieldset);

        return $inputfields;
    }
}
