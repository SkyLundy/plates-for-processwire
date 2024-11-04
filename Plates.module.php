<?php

declare(strict_types=1);

namespace ProcessWire;

use League\Plates\Engine;
use Plates\Extensions\PlatesAssistants;

class Plates extends WireData implements Module, ConfigurableModule
{

    public Engine $engine;

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
        $this->wire('classLoader')->addNamespace('Plates\Extensions', __DIR__ . '/Extensions');

        $this->engine = $this->initialize();
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
    public function ___initialize(?string $templatesDir = null): Engine
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
          'label' => 'Add additional assistant methods to Plates for use in template files?',
          'label2' => 'Add assistant methods',
          'checked' => $this->add_assistants ? 'checked' : '',
        ]);

        $inputfields->add([
          'type' => 'markup',
          'name' => 'assistant_examples',
          'label' => 'Assistant Functions',
          'value' => <<<EOT
            <p>Plates for ProcessWire can provide additional assistant functions to make working with data in your Plates template files easier and more efficient should you choose to include them. When rendering files using plates, in addition to having all ProcessWire objects available, additional functions are also made available.</p>
            <p>For more information about assistant function that can be included when Plates is loaded, refer to <code>/site/modules/Plates/Extensions/PlatesAssistance.php</code></p>
           EOT,
        ]);

        return $inputfields;
    }
}