<?php

/*
	PHP библиотека для разработки навыков Яндекс.Алисы
	Author: Sergey Avdeev <thesoultaker48@gmail.com>
	URL: https://github.com/thesoultaker48/yandex-dialogs-php
*/


include_once '../yandex-dialogs.class.php';

$alice = new YandexDialog();

// Все действия выполняем только если получили кореектные данные от Алисы
if($alice->get_request()) {

	// Начало диалога
	function _new_session($alice) {
		if($user_name = $alice->get_user_data('name')) {
			$alice->add_message('Привет '.$user_name.'!');
		} else {
			$alice->add_message('Привет! Как вас зовут?');
		}
	}
	$alice->bind_new_action('_new_session');
	
	// Неизвестная команда (считаем, что пользователь назвал свое имя)
	function _default($alice) {
		$alice->set_user_data('name', $alice->request['request']['command']);
		$alice->add_message('Рада знакомству!');
		$alice->end_session();
	}
	$alice->bind_default_action('_default');

	// Отправляем ответ и завершаем работу скрипта
	$alice->finish(true);
}

?>
