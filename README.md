# YANDEX-DIALOGS-PHP

PHP библиотека для разработки навыков Яндекс.Алисы
___

##  Содержание

 1. [История версий / TODO](#todo)
 2. [Примеры использования](#примеры)
 3. [Описание свойств](#свойства)
 	+ [request](#request)
	+ [response](#response)
 4. [Описание методов](#методы)
 	+ [Конструктор](#конструктор)
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
| Описание | Дата 
|:--:|--|
| Первая публичная версия | **08.01.2019**
| Отправка сообщений с изображениями | ---
| Подключение AppMetrica | ---

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

### get_request

### bind_new_action

### bind_words_action

### bind_percentage_action

### bind_default_action

### add_button

### add_message

### end_session

### finish
