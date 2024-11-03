<?php

declare(strict_types=1);

namespace ProcessWire;

use League\Plates\Engine;
use Plates\Extensions\PlatesAssistants;

final class Plates extends WireData implements Module, ConfigurableModule
{

    public Engine $engine;

    /**
     * {@inheritdoc}
     */
    public static function getModuleInfo()
    {
        return [
            'title' => __('Plates Templating', __FILE__),
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
        $this->wire('classLoader')->addNamespace('Plates\Extensions', __DIR__ . '/Extensions');

        $this->engine = $this->initializePlates();
    }

    /**
     * {@inheritdoc}
     */
    public function ready()
    {
        $this->registerProcessWireObjects($this->engine);

        if ($this->add_assistants) {
            $this->engine->loadExtension(new PlatesAssistants());
        }

        $this->wire->set('plates', $this->engine);
    }

    /**
     * Create the Plates object
     * @param  string|null $templatesDirectory Optional templates directory to specify if other
     *                                         direcory preferred.
     * @return Engine
     */
    public function ___initializePlates(?string $templatesDir = null): Engine
    {
        $templatesDir =  rtrim($templatesDir ?? $this->wire('config')->paths->templates, '/');

        return new Engine($templatesDir);
    }

    /**
     * Injects all ProcessWire object variables for use in Plates files at runtime
     * @param  Engine $engine Plates Engine object
     * @return Engine
     */
    private function registerProcessWireObjects(Engine $engine): Engine
    {
        foreach ($this->wire('all')->getArray() as $name => $object) {
            $engine->addData([$name => $object]);
        }

        return $engine;
    }

    /**
     * Create inputfields for module configuration
     * @param  InputfieldWrapper $inputfields InputfieldWrapper for module config page
     * @return InputfieldWrapper
     */
    public function getModuleConfigInputfields(InputfieldWrapper $inputfields): InputfieldWrapper
    {
        $inputfields->add([
          'type' => 'checkbox',
          'name' => 'add_assistants',
          'label' => 'Add additional assistant methods to Plates for use in template files',
          'checked' => $this->add_assistants ? 'checked' : '',
        ]);

        return $inputfields;
    }
}