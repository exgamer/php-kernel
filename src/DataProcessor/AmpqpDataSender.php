<?php

namespace Citizenzet\Php\Core\DataProcessor;

use Citizenzet\Php\Core\Components\Logger;
use Citizenzet\Php\Core\Components\ProgressBar;
use Citizenzet\Php\Core\Helpers\ArrayHelper;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Прием сообщений из rabbit mq
 *
 *  $config = [
 *     'dataHandlerClass' => AmqpDataHandler::class,
 *     'dataToSend' => [
 *          [
 *          
 *          ],
 *      ]
 * ];
 *
 *
 * AmpqpDataSender::exec($config);
 *
 * Class AmpqpDataSender
 * @package Citizenzet\Php\Core\DataProcessor
 * @author citizenzet <exgamer@live.ru>
 */
class AmpqpDataSender extends DataProcessor
{
    public $bySinglePage = true;

    /**
     * массив с данными для отправки в
     *
     * [
     *  [
     *
     *  ],
     *  [
     *  ]
     * ]
     * 
     * @var array 
     */
    public $dataToSend = [];

    public function init()
    {
        parent::init();
        if (! $this->dataHandler instanceof AmqpDataHandler ) {
            throw new Exception(get_class($this->dataHandler) . " must extend " . AmqpDataHandler::class);
        }
        
        if (! $this->dataToSend) {
            throw new Exception(get_class($this->dataHandler) . "dataToSend array must be set");
        }
    }

    public function execute()
    {
        $this->beforeExecute();
        if (! $this->isExecute())
        {
            return true;
        }
        
        $connection = new AMQPStreamConnection(... $this->dataHandler->getQuery());
        $channel = $connection->channel();
        $queueName = $this->dataHandler->getQueueName();
        $queueParams = $this->dataHandler->getQueueParams();
        array_unshift($queueParams, $queueName);
        $queueParams = array_values($queueParams);
        $channel->queue_declare(... $queueParams);
        Logger::info("Start sending messages");
        $bar = new ProgressBar(count($this->dataToSend));
        foreach(ArrayHelper::generator($this->dataToSend) as $data){
            $this->prepareModel($data);
            $this->prepareDataForSending($data);
            $msg = new AMQPMessage($data);
            $channel->basic_publish($msg, '', $queueName);
            $bar->update();
        }
        $channel->close();
        $connection->close();
        Logger::info("All messages sent");

        return true;
    }

    public function prepareDataForSending(&$data)
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}