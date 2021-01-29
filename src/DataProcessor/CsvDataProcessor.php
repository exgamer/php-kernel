<?php

namespace Citizenzet\Php\Core\DataProcessor;

use Exception;
use GuzzleHttp\Client;

/**
 * Class CsvDataProcessor
 *
 *  $config = [
 *     'dataHandlerClass' => CsvDataHandler::class,
 * ];
 *
 *
 * CsvDataProcessor::exec($config);
 *
 * @author Olzhas Kulzhambekov <exgamer@live.ru>
 */
class CsvDataProcessor extends DataProcessor
{
    public $bySinglePage = true;

    public function init()
    {
        parent::init();
        if (! $this->dataHandler instanceof CsvDataHandler ) {
            throw new Exception(get_class($this->dataHandler) . " must extend " . CsvDataHandler::class);
        }
    }

    public function execute()
    {
        $this->beforeExecute();
        if (! $this->isExecute())
        {
            return true;
        }

        $count = $this->getCsvRowsCount();
        $row = 1;
        if (($handle = fopen($this->dataHandler->getQuery(), "r")) !== FALSE) {
//            $this->outputSuccess( "START PROCESS" );
//            Console::startProgress(0, $count);
            while (($model = fgetcsv($handle, 0, $this->dataHandler->getDelimeter())) !== FALSE) {
                $this->prepareModel($model);
                $this->processModel($model);
                $this->finishProcessModel($model);
//                Console::updateProgress($row , $count);
                $row++;
            }
            fclose($handle);
            $memory = memory_get_usage()/1024;
//            $this->outputSuccess( "END PROCESS ; MEMORY USED: {$memory}");
        }

        $this->afterExecute();

        return true;
    }

    public function getCsvRowsCount()
    {
//        $this->outputSuccess( "Counting rows" );
        $rowCount = 1;
        $handle = fopen($this->dataHandler->getQuery(), "r");
        while(!feof($handle)){
            $line = fgets($handle);
            $rowCount++;
        }

        fclose($handle);
//        $this->outputSuccess( "File has " . $rowCount . " rows" );

        return $rowCount;
    }
}