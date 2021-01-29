<?php

namespace Citizenzet\Php\Core\DataProcessor;

use Exception;

/**
 * Class ExelDataHandler
 * @package citizenzet\yii2logic\dataprocessor
 * @author citizenzet <exgamer@live.ru>
 */
abstract class CsvDataHandler extends DataHandler
{
    /**
     * @return string
     * @throws Exception
     */
    public function getQuery()
    {
        throw new Exception("set csv file path");
    }

    /**
     * @return string
     */
    public function getDelimeter()
    {
        return ";";
    }
}