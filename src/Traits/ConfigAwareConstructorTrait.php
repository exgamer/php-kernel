<?php

namespace Citizenzet\Php\Core\Traits;

trait ConfigAwareConstructorTrait
{
    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                $this->{$name} = $value;
            }
        }
        $this->init();
    }

    public function init()
    {
    }
}