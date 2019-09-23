<?php

/*
	PHP библиотека для разработки навыков Яндекс.Алисы
	Author: Sergey Avdeev <avdeevsv91@gmail.com>
	URL: https://github.com/avdeevsv91/yandex-dialogs-php
*/

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
use YandexStation\YandexDialog;

$alice = new YandexDialog();

// Подключаем Google Chatbase
$alice->use_chatbase('YOU_CHATBASE_API_KEY');

// Все действия выполняем только если получили корректные данные от Алисы
if($alice->get_request()) {

	// Начало диалога
	function _new_session($alice) {
		$alice->add_button('Робот');
		$alice->add_message('Привет! Ты кто?');
	}
	$alice->bind_new_action('_new_session');

	// Ответ пользователя распознан
	function _robot($tokens, $alice) {
		$alice->add_message('Я так и знал!');
	}
	$alice->bind_words_action(['робот'], '_robot');

	// Неизвестная команда
	function _default($alice) {
		$alice->add_message('Я вас не понимаю! Вы, наверное, человек?');
		// Сообщаем Chatbase об этом
		$alice->chatbase_handled();
	}
	$alice->bind_default_action('_default');

	// Отправляем ответ и завершаем работу скрипта
	$alice->finish();
	exit;
}
