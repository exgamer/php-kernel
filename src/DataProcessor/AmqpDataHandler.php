<?php

namespace Citizenzet\Php\Core\DataProcessor;

use Exception;

/**
 * Class AmqpDataHandler
 * @package Citizenzet\Php\Core\DataProcessor
 * @author citizenzet <exgamer@live.ru>
 */
abstract class AmqpDataHandler extends DataHandler
{
    public function getQuery()
    {
        return [
            $this->getHost(),
            $this->getPort(),
            $this->getUser(),
            $this->getPassword(),
        ];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHost()
    {
        throw new Exception("set rabbitmq host");
    }

    public function getPort()
    {
        return 5672;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getUser()
    {
        throw new Exception("set rabbitmq user");
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPassword()
    {
        throw new Exception("set rabbitmq pass");
    }

    public function getQueueName()
    {
        throw new Exception("set queue name");
    }

    public function getQueueParams()
    {
        return [
            'passive'     => false,
            'durable'     => false,
            'exclusive'   => false,
            'auto_delete' => false,
        ];
    }

    public function getExchangeParams()
    {
        return [
            'type'        => 'direct', // more info at http://www.rabbitmq.com/tutorials/amqp-concepts.html
            'passive'     => false,
            'durable'     => false, // the exchange will survive server restarts
            'auto_delete' => false,
        ];
    }

    public function onMessageError($exception)
    {

    }
}