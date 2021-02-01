#Csv DataProcessor

Класс предназначенный для обработки csv файлов
с возможностью реализации логики обработки для каждой строки

1 для начала создадим класс с логикой обработки унаследованный 
    от Citizenzet\Php\Core\DataProcessor\CsvDataHandler
    и реализуем в нем логику обработки строк.
    В примере указаны 2 возможных метода DataHandler,
    методы можно посмотреть в 
    Citizenzet\Php\Core\DataProcessor\CsvDataHandler
    Citizenzet\Php\Core\DataProcessor\DataHandler
    
```php
<?php

namespace Console\App\DataHandlers;

use Citizenzet\Php\Core\DataProcessor\CsvDataHandler as Base;

class CsvDataHandler extends Base
{
    /**
     * указываем путь до csv файла
     * 
     * @return string
     */
    public function getQuery()
    {
        return __DIR__.'/../../data/big_data.csv';
    }

    /**
     * Метод для обработки записи параметром передается массив данных из строки файла
     * !!! в этом методе как и в других данные передаются по ссылке 
    * для возможности модификации в процессе обработки 
    * 
    * @param $data
     */
    public function processModel(&$data)
    {
//        dump($data);
        // можем записать какие то данные в хранилище
        $this->setData(function ($d) use ($data){
            $d['names'][] = $data['name'];

            return $d;
        });
    }
    
    /**
     * действия после завершения обработки всех записей файла
     */
    public function afterExecute()
    {
        //можем получить записанные данные из хранилища
        $data = $this->getData('names');
    }
}
   
``` 
    
2 Запуск обработки
```php
<?php

    $config = [
        'dataHandlerClass' => [
            'class' => CsvDataHandler::class,
        ],
    ];

    CsvDataProcessor::exec($config);

```

