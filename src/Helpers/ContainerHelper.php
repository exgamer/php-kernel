<?php

namespace Citizenzet\Php\Core\Helpers;

use ReflectionClass;
use ReflectionException;

/**
 * Class ContainerHelper
 * @package concepture\php\core\helper
 * @author Olzhas Kulzhambekov <exgamer@live.ru>
 */
class ContainerHelper
{
    /**
     * @param $config
     * @return object
     * @throws ReflectionException
     */
    public static function createObject($config)
    {
        $className = "";
        $arguments = [];
        if (is_string($config)){
            $className = $config;
        }
        if (is_array($config)){
            $className = ArrayHelper::getValue($config, 'class');
            $arguments = ArrayHelper::getValue($config, 'params', []);
        }
        $reflector = new ReflectionClass($className);
        if (!empty($arguments)){
            $arguments = [
                $arguments
            ];
        }
        return $reflector->newInstanceArgs($arguments);
    }

}