#AmqpDataProcessor

Класс предназначенный для обработки данных из очереди
с возможностью реализации логики обработки для каждой строки

1 для начала создадим класс с логикой обработки унаследованный 
    от Citizenzet\Php\Core\DataProcessor\AmqpDataHandler
    и реализуем в нем логику обработки строк.
    В примере указаны 2 возможных метода DataHandler,
    методы можно посмотреть в 
    Citizenzet\Php\Core\DataProcessor\AmqpDataHandler
    
```php
<?php

namespace Console\App\DataHandlers;

use Citizenzet\Php\Core\DataProcessor\CsvDataHandler as Base;

class AmqpDataHandler extends Base
{
    public function getHost()
    {
        return config('queue.connections.rabbitmq.host');
    }

    public function getUser()
    {
        return config('queue.connections.rabbitmq.login');
    }

    public function getPassword()
    {
        return config('queue.connections.rabbitmq.password');
    }

    public function getQueueName()
    {
        return QueueEnum::IMPORT_QUEUE;
    }

    /**
     * Метод получает одно сообщение из очереди для обработки
     * 
    * @param $msg
    */
    public function prepareModel(&$msg)
    {
        $data = json_decode($msg->body);
        $importData = $data->data;
        
        // ... какая то ваша логика
    }
    
    /**
    * @param  $msg
     */
    public function finishProcessModel(&$msg)
    {


    }
}
   
``` 
    
2 Запуск обработки
```php
<?php

    $config = [
        'dataHandlerClass' => [
            'class' => AmqpDataHandler::class,
        ],
    ];

    AmqpDataProcessor::exec($config);

```

