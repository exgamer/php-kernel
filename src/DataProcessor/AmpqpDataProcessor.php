<?php

namespace Citizenzet\Php\Core\DataProcessor;

use App\Models\ImportProduct;
use Citizenzet\Php\Core\Components\Logger;
use Citizenzet\Php\Core\Components\ProgressBar;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Прием сообщений из rabbit mq
 *
 *  $config = [
 *     'dataHandlerClass' => AmqpDataHandler::class,
 * ];
 *
 *
 * AmpqpDataProcessor::exec($config);
 *
 * Class AmpqpDataProcessor
 * @package Citizenzet\Php\Core\DataProcessor
 * @author citizenzet <exgamer@live.ru>
 */
class AmpqpDataProcessor extends DataProcessor
{
    public $bySinglePage = true;
    public $ackOnError = true;

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


        $connection = new AMQPStreamConnection($this->dataHandler->getHost(), $this->dataHandler->getPort(), $this->dataHandler->getUser(), $this->dataHandler->getPassword());
        $channel = $connection->channel();
        $queueName = $this->dataHandler->getQueueName();
//        $queueParams = $this->dataHandler->getQueueParams();
//        array_unshift($queueParams, $queueName);
//        $queueParams = array_values($queueParams);
        $channel->queue_declare($queueName,  false, false, false, false);
        Logger::info("Waiting for messages. To exit press CTRL+C");
        $callback = function ($msg) use ($channel, $queueName) {
            $originalMsg = clone $msg;
            Logger::info("Start process message");
            try {
                gc_collect_cycles();
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                $this->prepareModel($msg);
                $this->processModel($msg);
                $this->finishProcessModel($msg);
                $memory = memory_get_usage() / 1024;
                Logger::info("End process message; MEMORY USED: {$memory}");
            }catch (\Exception $ex) {
                //вернуть назад в очередь
//              $channel->basic_publish($originalMsg, '', $queueName);
                Logger::error($ex->getMessage());
                $this->onMessageError($ex);
                if ($this->ackOnError) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                }
            }
        };
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(
            $queueName,
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

    public function onMessageError($exception)
    {
        $this->dataHandler->onMessageError($exception);
    }
}