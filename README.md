# YANDEX-DIALOGS-PHP

PHP библиотека для разработки навыков Яндекс.Алисы
___

##  Содержание

 1. [История версий / TODO](#todo)
 2. [Примеры использования](#%D0%BF%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B)
 3. [Описание свойств](#%D1%81%D0%B2%D0%BE%D0%B9%D1%81%D1%82%D0%B2%D0%B0)
 	+ [request](#request) - Содержит информацию о запросе пользователя;
	+ [response](#response) - Содержит данные ответа на запрос пользователя;
 4. [Описание методов](#%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B)
 	+ [Конструктор](#%D0%BA%D0%BE%D0%BD%D1%81%D1%82%D1%80%D1%83%D0%BA%D1%82%D0%BE%D1%80) - Создание объекта класса YandexDialog;
	+ [get_request](#get_request) - Получить информацию о запросе пользователя;
	+ [get_some_text](#get_some_text) - Получает часть текста на основе заданных шаблонов;
	+ [bind_new_action](#bind_new_action) - Связывает указанную функцию с событием начала нового диалога (новой сессии);
	+ [bind_words_action](#bind_words_action) - Связывает указанную функцию с событием нахождения одного из заданных слов в запросе пользователя;
	+ [bind_percentage_action](#bind_percentage_action) - Связывает указанную функцию с событием превышения заданного процентного нахождения слов в запросе пользователя;
	+ [bind_suggestion_action](#bind_suggestion_action) - Связывает указанную функцию с событием превышения (или равенства) заданной процентной схожести предложения;
	+ [bind_default_action](#bind_default_action) - Связывает указанную функцию с собитием отсутствия других действий;
	+ [add_button](#add_button) - Добавляет кнопку в варианты ответа пользователя;
	+ [add_message](#add_message) - Добавляет сообщение в список ответов;
	+ [get_user_data](#get_user_data) - Получить значение переменной из данных пользователя;
	+ [set_user_data](#set_user_data) - Установить значение переменной в данных пользователя;
	+ [get_session_data](#get_session_data) - Получить значение переменной из сессии пользователя;
	+ [set_session_data](#set_session_data) - Установить значение переменной из сессии пользователя;
	+ [end_session](#end_session) - Завершает диалог с пользователем;
	+ [use_yametrika](#use_yametrika) - Включает использование сервиса Яндекс.Метрика;
	+ [yametrika_rgoal](#yametrika_rgoal) - Передача информации о достижении цели в Яндекс.Метрике;
	+ [use_chatbase](#use_chatbase) - Включает использование сервиса Google Chatbase;
	+ [chatbase_handled](#chatbase_handled) - Устанавливает значение флага handled для Google Chatbase;
	+ [finish](#finish) - Производит некоторые подготовительные процедуры и отправляет ответ Яндекс.Диалогам;
 4. [Встроенные теги](#%D0%B2%D1%81%D1%82%D1%80%D0%BE%D0%B5%D0%BD%D0%BD%D1%8B%D0%B5-%D1%82%D0%B5%D0%B3%D0%B8)
 5. [Используемые библиотеки](#%D0%B8%D1%81%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D1%83%D0%B5%D0%BC%D1%8B%D0%B5-%D0%B1%D0%B8%D0%B1%D0%BB%D0%B8%D0%BE%D1%82%D0%B5%D0%BA%D0%B8)

___
## TODO
| Описание изменений | Дата публикации 
|--|:--:|
|Первая публичная версия | **08.01.2019**
|Поддержка сервиса [Google Chatbase](#use_chatbase) | **09.01.2019**
|[Сохранение](#get_session_data)/[получение](#set_session_data) данных сессии | **05.02.2019**
|[Сохранение](#set_user_data)/[получение](#get_user_data) данных пользователя | **05.02.2019**
|Получение части текста по шаблону ([get_some_text](#get_some_text)) | **07.02.2019**
|Поддержка сервиса [Яндекс.Метрика](#use_yametrika) | **08.02.2019**
|Отправка [сообщений с изображениями](https://tech.yandex.ru/dialogs/alice/doc/resource-upload-docpage/) | ---
|Морфологический анализ слов с помощью [phpMorphy](https://github.com/heromantor/phpmorphy) | ---
|Поддержка сервиса [AppMetrica](https://appmetrica.yandex.ru/) | ---
|Поддержка сервиса [Google Analytics](https://analytics.google.com/) | ---
|Анализ текста с помощью [Томита-парсер](https://tech.yandex.ru/tomita/) | ---
|Вложенные нелинейные сценарии | ---
|Расширение списка [встроенных тегов](#%D0%B2%D1%81%D1%82%D1%80%D0%BE%D0%B5%D0%BD%D0%BD%D1%8B%D0%B5-%D1%82%D0%B5%D0%B3%D0%B8) | ---

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

`public function __construct(string $version='1.0')`

Создание объекта класса YandexDialog.

`$version` - Номер используемой версии протокола. Строка. Не обязательно. По умолчанию: '1.0'.

	$alice = new YandexDialog('1.0');

### get_request

`public function get_request(array $data=null): bool`

Получить информацию о запросе пользователя.

`$data` - Данные запроса пользователя. Многомерный массив ([см.тут](https://tech.yandex.ru/dialogs/alice/doc/protocol-docpage/#request)). Не обязательно. По умолчанию: декодированный json из php://input.

	$alice->get_request();

### get_some_text

`public function get_some_text(array $patterns, string $text=null): array`

Получает часть текста на основе заданных шаблонов. Метод возвращает ассоциативный массив, в котором в качестве ключей выступают имена паттернов, а значения заполнены найденными результатами. Если поиск не удался (оригинальный текст не подходит под указанные шаблоны, либо шаблоны составлены с ошибкой), то метод возвращает false.

`$patterns` - Массив шаблонов поиска. Подробнее см. в примере ниже. Массив. Обязательный параметр.

`$text` - Текст, в котором осуществляется поиск. Строка. Не обязательно. По-умолчанию: текст команды пользователя.

	$text = 'Меня зовут Иван Иванов. Мне сейчас 25 лет.';
	
	$name = $alice->get_some_text(array('Меня зовут {name1:word}', 'Мое имя {name1:word}'), $text); // ['name1' => 'Иван']
	// Если шаблон один, то можно передавать его строкой
	$name = $alice->get_some_text('Меня зовут {name1:word}', $text); // ['name1' => 'Иван']
	$name = $alice->get_some_text('Меня зовут {name2}.', $text); // ['name2' => 'Иван Иванов']
	$age = $alice->get_some_text('Мне {*}{age:int} лет', $text); // ['age' => 25]
	$results = $alice->get_some_text('Меня зовут {name:word}{*}Мне {*}{age:int} лет', $text); // ['name' => 'Иван', 'age' => 25]

### bind_new_action

`public function bind_new_action(string $action): bool`

Связывает указанную функцию с событием начала нового диалога (новой сессии). В вызываемую функцию передается указатель на $this.

`$action` - Имя функции, которой необходимо передавать управление. Строка. Обязательный параметр.

	function new_session_action($alice) {
		...
		some code
		...
	}
	$alice->bind_new_action('new_session_action');

### bind_words_action

`public function bind_words_action(array $words, string $action): bool`

Связывает указанную функцию с событием нахождения одного из заданных слов в запросе пользователя. В вызываемую функцию передается указатель на $this и обнаруженное слово.

`$words` - Слова для поиска (в нижнем регистре). Массив. Обязательный параметр;

`$action` - Имя функции, которой необходимо передавать управление. Строка. Обязательный параметр.

	function find_word_action($word, $alice) {
		...
		some code
		...
	}
	$alice->bind_words_action(array('яблоко', 'груша', 'апельсин'), 'find_word_action');

### bind_percentage_action

`public function bind_percentage_action(array $words, int $percentage, string $action): bool`

Связывает указанную функцию с событием превышения (или равенства) заданного процентного нахождения слов в запросе пользователя. В вызываемую функцию передается указатель на $this и найденное процентное значение.

`$words` - Слова для поиска (в нижнем регистре). Элементы так же могут быть массивами - в таком случае любое из совпадений внутри вложенного массива считается как совпадение всего массива. Массив. Обязательный параметр;

`$percentage` - Процентное значение, при достижении которого выполняется указанная функция. Число. Обязательный параметр;

`$action` - Имя функции, которой необходимо передавать управление. Строка. Обязательный параметр.

	function percentage_word_action($percent, $alice) {
		...
		some code
		...
	}
	$alice->bind_percentage_action(array(array('яблоко', 'груша'), 'апельсин'), 75, 'percentage_word_action');

### bind_suggestion_action

`public function bind_suggestion_action(string $text, int $percentage, string $action): bool`

Связывает указанную функцию с событием превышения (или равенства) заданной процентной схожести предложения. В вызываемую функцию передается указатель на $this и найденное процентное значение. Работает на основе [bind_percentage_action](#bind_percentage_action).

`$text` - Предложение для сравнения схожести. Строка. Обязательный параметр;

`$percentage` - Процентное значение, при достижении которого выполняется указанная функция. Число. Обязательный параметр;

`$action` - Имя функции, которой необходимо передавать управление. Строка. Обязательный параметр.

	function suggestion_action($percent, $alice) {
		...
		some code
		...
	}
	$alice->bind_suggestion_action('Очистить список покупок', 75, 'suggestion_action');

### bind_default_action

`public function bind_default_action(string $action): bool`

Связывает указанную функцию с собитием отсутствия других действий. Отлично подходит для обработки ситуаций, когда фраза пользователя не распознана. В вызываемую функцию передается указатель на $this.

`$action` - Имя функции, которой необходимо передавать управление. Строка. Обязательный параметр.

	function default_action($alice) {
		...
		some code
		...
	}
	$alice->bind_default_action('default_action');

### add_button

`public function add_button(string $title, string $url=null, array $payload=null, bool $hide=false): bool`

Добавляет кнопку в варианты ответа пользователя.

`$title` - Текст кнопки, который будет отправлен навыку по ее нажатию. Строка. Обязательный параметр;

`$url` - URL, который должна открывать кнопка. Строка. Не обязательно. По-умолчанию: null.

`$payload` - Произвольные данные, которые Яндекс.Диалоги должны отправить обработчику, если данная кнопка будет нажата. Массив. Не обязательно. По-умолчанию: null.

`$hide` - Признак того, что кнопку нужно убрать после следующей реплики пользователя. Логическое. Не обязательно. По-умолчанию: false.

	$alice->add_button('Открой Яндекс', 'http://yandex.ru');
	$alice->add_button('Сделать заказ', null, array('item' => 42, 'price' => 100), true);

### add_message

`public function add_message(string $message, string $tts=null): bool`

Добавляет сообщение в список ответов. Перед отправкой автоматически выбирается один случайный вариант.

`$message` - Текст сообщения для отправки. Могут использоваться теги. Строка. Обязательный параметр;

`$tts` - Текстовое TTS представление для отзвучивания сообщения ([см.тут](https://tech.yandex.ru/dialogs/alice/doc/speech-tuning-docpage/#speech-tuning)). Могут использоваться теги. Можно использовать [звуки](https://tech.yandex.ru/dialogs/alice/doc/sounds-docpage/). Строка. Не обязательно. По-умолчанию: $message.

	$alice->add_message('Среди этих двух сообщений');
	$alice->add_message('Будет выбрано только одно', 'уже выбрано только одно');

### get_user_data

`public function get_user_data(string $name): string`

Получить значение переменной из данных пользователя.

`$name` - Имя переменной, значение которой необходимо получить. Строка. Обязательный параметр;

	$user_name = $alice->get_user_data('name');

### set_user_data

`public function set_user_data(string $name, mixed $value): bool`

Установить значение переменной в данных пользователя.

`$name` - Имя переменной, значение которой необходимо установить. Строка. Обязательный параметр;

`$value` - Значение переменной, которое необходимо установить. Если задать null, то переменная удалится. Любой тип. Обязательный параметр;

	$alice->set_user_data('name', 'Иван');
	$alice->set_user_data('items', array('Яблоки', 'Бананы', 'Молоко'));
	$alice->set_user_data('notes', null);

### get_session_data

`public function get_session_data(string $name): string`

Получить значение переменной из сессии пользователя.

`$name` - Имя переменной, значение которой необходимо получить. Строка. Обязательный параметр;

	$action = $alice->get_session_data('action');

### set_session_data

`public function set_session_data(string $name, mixed $value): bool`

Установить значение переменной из сессии пользователя.

`$name` - Имя переменной, значение которой необходимо установить. Строка. Обязательный параметр;

`$value` - Значение переменной, которое необходимо установить. Если задать null, то переменная удалится. Любой тип. Обязательный параметр;

	$alice->set_session_data('action', 'start');
	$alice->set_session_data('progress', array('step' => 2, 'place' => 'home'));
	$alice->set_session_data('items', null);

### end_session

`public function end_session(): bool`

Завершает диалог с пользователем.

	$alice->end_session();

### use_yametrika

`public function use_yametrika(string $counter_id)`

Включает использование сервиса [Яндекс.Метрика](https://metrika.yandex.ru/). Данные передаются в момент вызова метода [finish](#finish).

`$counter_id` - ID счетчика, полученный в личном кабинете сервиса. Строка. Обязательный параметр.

	$alice->use_yametrika('YOU_COUNTER_ID');

### yametrika_rgoal

`public function yametrika_rgoal(string $target): bool`

Передача информации о достижении цели в Яндекс.Метрике. Более подробно читайте в [официальной документации сервиса](https://yandex.ru/support/metrika/general/goal-js-event.html#js-event).

`$target` - Идентификатор цели. Строка. Обязательный параметр.

	$alice->yametrika_rgoal('my_target');

### use_chatbase

`public function use_chatbase(string $api_key)`

Включает использование сервиса [Google Chatbase](https://chatbase.com/). Данные передаются в момент вызова метода [finish](#finish).

`$api_key` - API ключ, полученный в личном кабинете сервиса. Строка. Обязательный параметр.

	$alice->use_chatbase('YOU_CHATBASE_API_KEY');

### chatbase_handled

`public function chatbase_handled(bool $handled=true): bool`

Устанавливает значение флага handled для Google Chatbase. Этим способом рекомендуется "маркировать" те ситуации, когда запрос пользователя не распознан или привел к ошибке. Если не вызывать этот метод, то считается что handled = false.

`$handled` - Значение, которое необходимо установить для handled. Логическое. Не обязательно. По-умолчанию: true.

	$alice->chatbase_handled();

### finish

`public function finish(bool $return=false): bool`

Производит некоторые подготовительные процедуры и отправляет ответ Яндекс.Диалогам.

`$return` - Возвратить ответ (вместо вывода на экран). Логическое. Не обязательно. По-умолчанию: false.

	$alice->finish();

## Встроенные теги

Некоторые методы (например такие как [add_message](#add_message)) позволяют использовать встроенные теги. На данный момент доступен всего один, но со временем добавятся еще:

`[слово1|слово2|...|словоN]` - Выбрать одну строку случайным образом среди перечисленных.

## Используемые библиотеки

Chatbase PHP (форк): https://github.com/thesoultaker48/chatbase-php

Server YaMetrika (форк): https://github.com/thesoultaker48/server_yametrika
