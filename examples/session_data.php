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
		$alice->add_button('Да');
		$alice->add_button('Нет');
		$alice->add_message('Здравствуйте! Вы любите зиму?');
	}
	$alice->bind_new_action('_new_session');
	
	// Пользователь ответил "нет" - сохраним эти данные в переменной "winter"
	function _no($tokens, $alice) {
		$alice->add_message('Понятно! Мне, если честно, холод тоже не по душе! А где вы живете?');
		$alice->set_session_data('winter', 'no');
	}
	$alice->bind_words_action(['не', 'нет'], '_no');
	
	// Пользователь ответил "да" - сохраним эти данные в переменной "winter"
	function _yes($tokens, $alice) {
		$alice->add_message('Вот как? Тогда расскажите, за что вы ее любите?');
		$alice->set_session_data('winter', 'yes');
	}
	$alice->bind_words_action(['да', 'люблю'], '_yes');

	// Неизвестная команда (получим информацию о предыдущих ответах пользователя)
	function _default($alice) {
		if($alice->get_session_data('winter') == 'no') {
			$alice->add_message('Я ничего не поняла, кроме того что вы не любите зиму!');
			$alice->set_session_data('winter', null); // Удалим переменную сессии. А так как больше наша сессия не содержит никаких данных, то ее файл удалится автоматически.
			$alice->end_session(); // Закрываем диалог с пользователем
		} elseif($alice->get_session_data('winter') == 'yes') {
			$alice->add_message('Я ничего не поняла, кроме того что вам нравится зима!');
			$alice->set_session_data('winter', null);
			$alice->end_session();
		} else {
			$alice->add_message('Это все конечно хорошо, но скажите - вы любите зиму?');
		}
	}
	$alice->bind_default_action('_default');

	// Отправляем ответ и завершаем работу скрипта
	$alice->finish();
	exit;
}
