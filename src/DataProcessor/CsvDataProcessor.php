<?php

namespace Citizenzet\Php\Core\DataProcessor;

use Citizenzet\Php\Core\Components\Logger;
use Citizenzet\Php\Core\Components\ProgressBar;
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
            Logger::info("START PROCESS");
            $bar = new ProgressBar($count);
            while (($model = fgetcsv($handle, 0, $this->dataHandler->getDelimeter())) !== FALSE) {
                $this->prepareModel($model);
                $this->processModel($model);
                $this->finishProcessModel($model);
                $bar->update();
                $row++;
            }
            fclose($handle);
            $memory = memory_get_usage()/1024;
            echo PHP_EOL;
            Logger::info("END PROCESS ; MEMORY USED: {$memory}");
        }

        $this->afterExecute();

        return true;
    }

    public function getCsvRowsCount()
    {
        Logger::info("Counting rows" );
        $rowCount = 1;
        $handle = fopen($this->dataHandler->getQuery(), "r");
        while(!feof($handle)){
            $line = fgets($handle);
            $rowCount++;
        }

        fclose($handle);
        Logger::info("File has " . $rowCount . " rows"  );

        return $rowCount;
    }
}