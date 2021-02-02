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
}