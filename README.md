# YANDEX-DIALOGS-PHP

PHP библиотека для разработки навыков Яндекс.Алисы
___

##  Содержание

 1. [История версий / TODO](#todo)
 2. [Примеры использования](#%D0%BF%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B)
 3. [Описание свойств](#%D1%81%D0%B2%D0%BE%D0%B9%D1%81%D1%82%D0%B2%D0%B0)
 	+ [request](#request)
	+ [response](#response)
 4. [Описание методов](#%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B)
 	+ [Конструктор](#%D0%BA%D0%BE%D0%BD%D1%81%D1%82%D1%80%D1%83%D0%BA%D1%82%D0%BE%D1%80)
	+ [get_request](#get_request)
	+ [bind_new_action](#bind_new_action)
	+ [bind_words_action](#bind_words_action)
	+ [bind_percentage_action](#bind_percentage_action)
	+ [bind_default_action](#bind_default_action)
	+ [add_button](#add_button)
	+ [add_message](#add_message)
	+ [end_session](#end_session)
	+ [finish](#finish)

___
## TODO
| Описание изменений | Дата публикации 
|:--:|--|
|Первая публичная версия | **08.01.2019**
|Отправка сообщений с изображениями | ---
|Сохранение данных сессии | ---
|Сохранение данных пользователя | ---
|Морфологический анализ слов | ---
|Подключение Яндекс.Метрика | ---
|Подключение AppMetrica | ---
|Подключение Google Chatbase | ---
|Подключение Google Analytics | ---
|Анализ текста с помощью Томита-парсер | ---

## Примеры
Примеры использования библиотеки находятся в папке [examples](/thesoultaker48/yandex-dialogs-php/tree/master/examples).

## Свойства
Не рекомендуется прямое изменение свойств объектов класса YandexDialog. Делайте это через соответствующие методы.

### request
Содержит информацию о запросе пользователя. Более подробно можно почитать в [официальной документации](https://tech.yandex.ru/dialogs/alice/doc/protocol-docpage/#request) к протоколу.

### response
Содержит данные ответа на запрос пользователя. Более подробно можно почитать в [официальной документации](https://tech.yandex.ru/dialogs/alice/doc/protocol-docpage/#response) к протоколу.

## Методы

### Конструктор
Создание объекта класса YandexDialog. В качестве единственного параметра может принимать номер версии используемого протокола (не обязательно, по-умолчанию 1.0).

    $alice = new YandexDialog('1.0');

### get_request

### bind_new_action

### bind_words_action

### bind_percentage_action

### bind_default_action

### add_button

### add_message

### end_session

### finish
