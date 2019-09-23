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
		$alice->add_message('Здравствуйте! Как вас зовут?');
	}
	$alice->bind_new_action('_new_session');

	// Действие по умолчанию
	function _default($alice) {
		$name = null;
		if($name = $alice->get_template_text(['меня зовут {name:word}', 'мое имя {name:word}'])) { // Получаем имя из фразы
			$name = $name['name'];
		} elseif(count($alice->request['request']['nlu']['tokens']) == 1) { // Если было названо только одно слово, то считаем что это имя
			$name = $alice->request['request']['command'];
		}
		// Отвечаем пользователю
		if($name) {
			$alice->add_message('Рада с вами познакомиться, '.$name.'!');
			$alice->end_session();
		} else {
			$alice->add_message('Извините, но я хочу узнать как вас зовут?');
		}
	}
	$alice->bind_default_action('_default');

	// Отправляем ответ и завершаем работу скрипта
	$alice->finish();
	exit;
}
