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
	+ [get_user_data](#get_user_data)
	+ [set_user_data](#set_user_data)
	+ [get_session_data](#get_session_data)
	+ [set_session_data](#set_session_data)
	+ [end_session](#end_session)
	+ [use_chatbase](#use_chatbase)
	+ [chatbase_handled](#chatbase_handled)
	+ [finish](#finish)
 4. [Встроенные теги](#%D0%B2%D1%81%D1%82%D1%80%D0%BE%D0%B5%D0%BD%D0%BD%D1%8B%D0%B5-%D1%82%D0%B5%D0%B3%D0%B8)

___
## TODO
| Описание изменений | Дата публикации 
|--|:--:|
|Первая публичная версия | **08.01.2019**
|Поддержка сервиса [Google Chatbase](#use_chatbase) | **09.01.2019**
|Сохранение данных сессии | **05.02.2019**
|[Сохранение](#set_user_data)/[получение](#get_user_data) данных пользователя | **05.02.2019**
|Отправка сообщений с изображениями | ---
|Морфологический анализ слов | ---
|Подключение Яндекс.Метрика | ---
|Подключение AppMetrica | ---
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

`public function __construct(string $version='1.0')`

Создание объекта класса YandexDialog.

`$version` - Номер используемой версии протокола. Строка. Не обязательно. По умолчанию: '1.0'.

	$alice = new YandexDialog('1.0');

### get_request

`public function get_request(array $data=null): bool`

Получить информацию о запросе пользователя.

`$data` - Данные запроса пользователя. Многомерный массив ([см.тут](https://tech.yandex.ru/dialogs/alice/doc/protocol-docpage/#request)). Не обязательно. По умолчанию: декодированный json из php://input.

	$alice->get_request();

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

`public function add_button(string $title, string $url=NULL, array $payload=NULL, bool $hide=false): bool`

Добавляет кнопку в варианты ответа пользователя.

`$title` - Текст кнопки, который будет отправлен навыку по ее нажатию;

`$url` - URL, который должна открывать кнопка;

`$payload` - Произвольные данные, которые Яндекс.Диалоги должны отправить обработчику, если данная кнопка будет нажата;

`$hide` - Признак того, что кнопку нужно убрать после следующей реплики пользователя.

	$alice->add_button('Открой Яндекс', 'http://yandex.ru');
	$alice->add_button('Сделать заказ', NULL, array('item' => 42, 'price' => 100), true);

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

`public function finish(bool $die=false): bool`

Производит некоторые подготовительные процедуры и отправляет ответ Яндекс.Диалогам.

`$die` - Завершить работу скрипта после отправки. Логическое. Не обязательно. По-умолчанию: false.

	$alice->finish(true);

## Встроенные теги

Некоторые методы (например такие как [add_message](#add_message)) позволяют использовать встроенные теги. На данный момент доступен всего один, но со временем добавятся еще:

`[слово1|слово2|...|словоN]` - Выбрать одну строку случайным образом среди перечисленных.
