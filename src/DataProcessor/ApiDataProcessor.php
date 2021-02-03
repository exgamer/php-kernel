<?php

namespace Citizenzet\Php\Core\DataProcessor;

use Citizenzet\Php\Core\Enums\HttpCodeEnum;
use GuzzleHttp\Client;
use Exception;

/**
 * Class DataProcessor
 *
 *  $config = [
 *     'dataHandlerClass' => ApiSitemapDataHandler::class,
 * ];
 *
 *  $config = [
 *     'dataHandlerClass' => [
 *         'class' => ApiBookmakerRatingRecountDataHandler::class,
 *         'someVar' => 12
 *      ],
 * ];
 *
 * ApiDataProcessor::exec($config);
 *
 * @package concepture\yii2logic\dataprocessor
 * @author Olzhas Kulzhambekov <exgamer@live.ru>
 */
class ApiDataProcessor extends DataProcessor
{
    public $bySinglePage = true;

    public function init()
    {
        parent::init();
        if (! $this->dataHandler instanceof ApiDataHandler ) {
            throw new Exception(get_class($this->dataHandler) . " must extend " . ApiDataHandler::class);
        }
    }

    public function execute()
    {
        $client = new Client(['timeout' => 0]);
        $res = $client->request($this->dataHandler->method, $this->dataHandler->getQuery(), $this->dataHandler->queryConfig);
        $this->dataHandler->responseStatus = $res->getStatusCode();
        if (in_array($res->getStatusCode(), [HttpCodeEnum::OK, HttpCodeEnum::CREATED])){
//            $data = json_decode($res->getBody()->getContents(), true);
            $this->dataHandler->responseBodyContent = $res->getBody()->getContents();
        }
    }

    public function getResponseStatus()
    {
        return $this->dataHandler->responseStatus;
    }

    public function getResponseBodyContent()
    {
        return $this->dataHandler->responseBodyContent;
    }
}