<?php

namespace Citizenzet\Php\Core\DataProcessor;

use Citizenzet\Php\Core\Components\Logger;
use Citizenzet\Php\Core\Components\ProgressBar;
use Citizenzet\Php\Core\Helpers\ContainerHelper;
use Citizenzet\Php\Core\Traits\ConfigAwareConstructorTrait;
use Citizenzet\Php\Core\Traits\DataTrait;

/**
 * Class DataProcessor
 *
 *  $config = [
 *     'dataHandlerClass' => SitemapDataHandler::class,
 *     'pageSize' => 5
 * ];
 *
 *  $config = [
 *     'dataHandlerClass' => [
 *         'class' => BookmakerRatingRecountDataHandler::class,
 *         'someVar' => 12
 *      ],
 *     'pageSize' => 5
 * ];
 *
 * DataProcessor::exec($config);
 *
 * @author Olzhas Kulzhambekov <exgamer@live.ru>
 */
class DataProcessor
{
    use ConfigAwareConstructorTrait;
    use DataTrait;

    public $dataHandlerClass;
    /**
     * @var DataHandler
     */
    public $dataHandler;
    public $queryCondition;
    public $pageSize = 50;
    public $isDone = false;
    public $totalCount;
    public $currentPage = 0;
    public $targetPage = 0;
    public $bySinglePage = false;

    /** @var \DateTime Время начала выполнения скрипта */
    protected $timeStart;

    public function printMemoryUsage($message = '')
    {
        $memory = memory_get_usage();
        Logger::info("MEMORY USED : " . ($memory/(1024) ) . " - " . $message);
    }

    public static function exec($config)
    {
        $processor = new static($config);
        $processor->execute();
    }

    public function init()
    {
        $this->dataHandler = ContainerHelper::createObject($this->dataHandlerClass);
        $this->dataHandler->setProcessor($this);
        $this->timeStart =  date('Y-m-d H:i:s');
    }

    /**
     * выполняет запрос и обработку данных
     *
     * @param array $inputData
     * @return type
     */
    public function execute()
    {
        $this->beforeExecute();
        if (! $this->isExecute())
        {
            return true;
        }

        do {
            try{
                gc_collect_cycles();
                $this->_execute();
            } catch (\Exception $dbEx){
                Logger::error($dbEx->getMessage());
                $this->noDbConnectionExceptionActions([], $dbEx);
                continue;
            }
        } while (! $this->isDone());

        $this->afterExecute();

        return true;
    }

    /**
     * exec query
     *
     *
     * @return boolean
     */
    public function _execute()
    {
        $models = $this->executeQuery();
        $pagesCount = ceil($this->totalCount/$this->pageSize);
        $currentPage = $this->currentPage;
        if ($pagesCount == 0) {
            $currentPage = 0;
        }

        Logger::error("START PROCESS PAGE : " . $currentPage . " of " . $pagesCount);
        $this->beforePageProcess();
        $count = count($models);
        $bar = new ProgressBar($count);
        foreach ($models as $k => $model) {
            try{
                $this->prepareModel($model);
                $this->processModel($model);
                $this->finishProcessModel($model);
                $bar->update();
            } catch (\Exception $dbEx){
                $this->noDbConnectionExceptionActions($model, $dbEx);
                continue;
            }
        }
        $this->afterPageProcess();
        if ($this->bySinglePage){
            $this->isDone = true;
        }

        echo PHP_EOL;
        $models = null;
        $memory = memory_get_usage()/1024;
        Logger::error("END PROCESS PAGE : "  . $currentPage . " of " . $pagesCount . "; MEMORY USED: {$memory}");

        return true;
    }

    /**
     *
     * @return boolean
     */
    public function isDone()
    {

        return $this->isDone || $this->bySinglePage;
    }

    protected function getQuery()
    {
        return $this->dataHandler->getQuery();
    }

    /**
     *  get rows by sql
     */
    protected function executeQuery()
    {
        //Переделать абстрагировать
        throw new \Exception("Не адаптировано по ларавел");
        $query = $this->dataHandler->getQuery();
        $this->dataHandler->setupQuery($query);
        $condition = $this->queryCondition;
        if (is_callable($condition)){
            call_user_func($condition, $query);
        }

        $config = [
            'pagination' => [
                'pageSize' => $this->pageSize,
                'pageSizeParam' => false,
                'forcePageParam' => false,
                'page' => $this->currentPage
            ],
            'query' => $query
        ];
        $dataProvider = $this->dataHandler->getDataProvider($config );
        $models = $dataProvider->getModels();
        if (! $this->totalCount) {
            $this->totalCount = $dataProvider->getTotalCount();
        }

        $this->currentPage = $dataProvider->getPagination()->getPage();
        if ($this->currentPage+1 == $dataProvider->getPagination()->getPageCount()){
            $this->isDone = true;
        }

        if ($dataProvider->getCount() == 0){
            $this->isDone = true;
        }

        $this->currentPage +=1;
        $query = null;
        $config = null;
        $dataProvider = null;

        return $models;
    }

    /**
     * return total row count
     *
     * @return integer
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Действия до запуска query
     */
    public function beforeExecute()
    {
        $this->dataHandler->beforeExecute();
    }

    /**
     * дествия после завершения всех запросов
     */
    public function afterExecute()
    {
        $this->dataHandler->afterExecute();
    }

    /**
     *
     * @return boolean
     */
    public function isExecute()
    {
        return $this->dataHandler->isExecute($this);
    }

    /**
     * Действия после завершения операции
     * @param type $data
     */
    public function finishProcessModel(&$data)
    {
        $this->dataHandler->finishProcessModel($data);
    }

    /**
     * Действия после завершения обработки 1 страницы данных
     */
    public function beforePageProcess()
    {
        $this->dataHandler->beforePageProcess();
    }

    /**
     * Действия после завершения обработки 1 страницы данных
     */
    public function afterPageProcess()
    {
        $this->dataHandler->afterPageProcess();
    }

    /**
     * Дейсвия при недоступности БД
     *
     * @param array $data
     * @param \yii\db\Exception $exception
     */
    public function noDbConnectionExceptionActions($data, $exception)
    {
        Logger::error( " ОШИБКА!!!  ".$exception->getMessage().PHP_EOL);
    }

    /**
     * returns array of prepared data
     * вносим необходимые изменения в данные
     *
     * return array
     */
    public function prepareModel(&$data)
    {
        $this->dataHandler->prepareModel( $data);
    }

    /**
     * Выполняем необходимые действия с данными
     */
    public function processModel(&$data)
    {
        $this->dataHandler->processModel($data);
    }
}