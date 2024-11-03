<?php

declare(strict_types=1);

namespace ProcessWire;

use League\Plates\Engine;
use Plates\Extensions\PlatesAssistants;

final class Plates extends WireData implements Module
{

    public Engine $engine;

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

    public function init()
    {
        $this->wire('classLoader')->addNamespace('Plates\Extensions', __DIR__ . '/Extensions');
        // $templates = rtrim($this->wire('config')->paths->templates, '/');

        // $this->engine = new Engine($templates);
        $this->engine = $this->initializePlates();
    }

    public function ready()
    {

        $this->registerProcessWireObjects($this->engine);

        $this->engine->loadExtension(new PlatesAssistants());

        $this->wire->set('plates', $this->engine);
    }

    /**
     * Create the Plates object
     * @param  string|null $templatesDirectory Optional templates directory to specify
     * @return Engine
     */
    public function ___initializePlates(?string $templatesDir = null): Engine
    {
        $templatesDir =  rtrim($templatesDir ?? $this->wire('config')->paths->templates, '/');

        return new Engine($templatesDir);
    }

    private function registerProcessWireObjects(Engine $engine): Engine
    {
        foreach ($this->wire('all')->getArray() as $name => $object) {
            $engine->addData([$name => $object]);
        }

        return $engine;
    }
}