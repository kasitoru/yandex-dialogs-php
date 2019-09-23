<?php

/*
	PHP библиотека для разработки навыков Яндекс.Алисы
	Author: Sergey Avdeev <avdeevsv91@gmail.com>
	URL: https://github.com/avdeevsv91/yandex-dialogs-php
*/

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
use YandexStation\YandexDialog;

$alice = new YandexDialog();

/*
	При запросе "Алиса, узнай у <имя_навыка> какая сегодня погода?" навык ответит "Отличная!" и завершит сессию.
	Если запустить навык обычным способом ("Алиса, запусти навык <имя_навыка>"), сессия завершаться не будет.
*/

// Все действия выполняем только если получили корректные данные от Алисы
if($alice->get_request()) {

	// Обработка начала диалога
	if(!$alice->is_cmd_start()) { // Только если навык запущен без переданной команды
		function _new_session($alice) {
			$alice->add_message('Здравствуйте! Я тестовый навык.');
		}
		$alice->bind_new_action('_new_session');
	} else { // Если же навык запущен с помощью "Алиса, скажи <имя_навыка> <действие>" и других подобных команд
		$alice->end_session(); // Установим флаг окончания сессии и попытаемся обработать эту команду ниже
	}
	
	// Обработка запроса пользователя
	function _weather($percent, $alice) {
		if($alice->is_new_session()) {
			$alice->add_message('Отличная!');
		} else {
			$alice->add_message('Ужасная!');
		}
	}
	$alice->bind_sentence_action('какая сегодня погода', 60, '_weather');

	// Неизвестная команда
	function _default($alice) {
		$alice->add_message('Я вас не понимаю!');
	}
	$alice->bind_default_action('_default');

	// Отправляем ответ и завершаем работу скрипта
	$alice->finish();
	exit;
}
