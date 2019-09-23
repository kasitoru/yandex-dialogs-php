<?php

/*
	PHP библиотека для разработки навыков Яндекс.Алисы
	Author: Sergey Avdeev <avdeevsv91@gmail.com>
	URL: https://github.com/avdeevsv91/yandex-dialogs-php
*/

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
use YandexStation\YandexDialog;

$alice = new YandexDialog();

// Все действия выполняем только если получили корректные данные от Алисы
if($alice->get_request()) {

	// Начало диалога
	function _new_session($alice) {
		$alice->add_message('Здравствуйте! Как Вас зовут?');
	}
	$alice->bind_new_action('_new_session');
	
	// Пользователь представился
	function _username($patterns, $alice) {
		$alice->add_message('Привет '.$patterns['name'].'!');
	}
	$alice->bind_template_action(['Меня зовут {name:word}', 'Мое имя {name:word}'], '_username');

	// Неизвестная команда
	function _default($alice) {
		$alice->add_message('Я вас не понимаю!');
	}
	$alice->bind_default_action('_default');

	// Отправляем ответ и завершаем работу скрипта
	$alice->finish();
	exit;
}
