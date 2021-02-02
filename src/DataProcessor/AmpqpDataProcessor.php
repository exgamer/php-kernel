<?php

namespace Citizenzet\Php\Core\DataProcessor;

use App\Models\ImportProduct;
use Citizenzet\Php\Core\Components\Logger;
use Citizenzet\Php\Core\Components\ProgressBar;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Прием сообщений из rabbit mq
 *
 *  $config = [
 *     'dataHandlerClass' => AmqpDataHandler::class,
 * ];
 *
 *
 * CsvDataProcessor::exec($config);
 *
 * Class AmpqpDataProcessor
 * @package Citizenzet\Php\Core\DataProcessor
 * @author citizenzet <exgamer@live.ru>
 */
class AmpqpDataProcessor extends DataProcessor
{
    public $bySinglePage = true;

    public function init()
    {
        parent::init();
        if (! $this->dataHandler instanceof AmqpDataHandler ) {
            throw new Exception(get_class($this->dataHandler) . " must extend " . AmqpDataHandler::class);
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
        $queueParams = array_unshift($this->dataHandler->getQueueParams(), $this->dataHandler->getQueueName());
        $count = $channel->queue_declare(... $queueParams);
        Logger::info("Waiting for messages. To exit press CTRL+C");
        $callback = function ($msg)  {
            Logger::info("Start process message");
            $this->prepareModel($msg);
            $this->processModel($msg);
            $this->finishProcessModel($msg);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            $memory = memory_get_usage()/1024;
            Logger::info("End process message; MEMORY USED: {$memory}");
        };
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(
            $this->dataHandler->getQueueName(), 
            '', 
            false, 
            false, 
            false, 
            false, 
            $callback);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
        
        return true;
    }
}