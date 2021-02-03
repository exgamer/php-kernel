<?php

namespace Citizenzet\Php\Core\DataProcessor;

use Exception;

/**
 * Вспомогательный класс для обработки данных полученных через апи
 *
 * @author CitizenZet
 */
abstract class ApiDataHandler extends DataHandler
{
    public $method = 'GET';
    public $queryConfig = [];

    /**
     * @return string
     * @throws Exception
     */
    public function getQuery()
    {
        throw new Exception("set url");
    }
}