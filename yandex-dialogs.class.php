<?php

/*
	PHP библиотека для разработки навыков Яндекс.Алисы
	Author: Sergey Avdeev <thesoultaker48@gmail.com>
	URL: https://github.com/thesoultaker48/yandex-dialogs-php
*/


class YandexDialog {
	
	public $request = null;
	public $response = null;

	// Конструктор
	public function __construct($version='1.0') {
		$this->response = array(
			'response' => array(
				'text' => null,
				'tts' => null,
				'buttons' => array(),
				'end_session' => false
			),
			'session' => array(
				'session_id' => null,
				'message_id' => null,
				'user_id' => null
			),
			'version' => $version
		);
	}
	
	// Получаем данные от пользователя
	public function get_request($data=null) {
		if(is_null($data)) {
			$this->request = json_decode(trim(file_get_contents('php://input')), true);
		} else {
			$this->request = $data;
		}
		if(isset($this->request['version'])) {
			if($this->request['session']['new']) {
				if(in_array($this->request['request']['original_utterance'], array('ping', 'test'))) {
					$this->add_message('ok');
					$this->finish(true);
				}
			}
			$this->response['session']['session_id'] = $this->request['session']['session_id'];
			$this->response['session']['message_id'] = $this->request['session']['message_id'];
			$this->response['session']['user_id'] = $this->request['session']['user_id'];
			return true;
		} else {
			return false;
		}
	}
	
	// Действие, выполняемое при старте новой сессии
	public function bind_new_action($action) {
		if(empty($this->response['response']['text'])) {
			if($this->request['session']['new']) {
				$action($this);
				return true;
			}
		}
		return false;
	}
	
    // Действие, выполняемое при наличии определенных слов
    public function bind_words_action($words, $action) {
		if(empty($this->response['response']['text'])) {
			if($tokens = $this->request['request']['nlu']['tokens']) {
                foreach($tokens as $token) {
                    if(in_array($token, $words)) {
                        return $action($token, $this);
                        break;
                    }
                }
			}
		}
		return false;
    }
	
	// Действие, выполняемое при удовлетворении процентного содержания определенных слов
    public function bind_percentage_action($words, $percentage, $action) {
		if(empty($this->response['response']['text'])) {
			if($tokens = $this->request['request']['nlu']['tokens']) {
				$matches = 0;
				foreach($words as $word) {
					foreach($tokens as $token) {
						if(is_array($word)) {
							if(in_array($token, $word)) {
								$matches++;
								break;
							}
						} else {
							if($token == $word) {
								$matches++;
								break;
							}
						}
					}
				}
                $match = $matches/(count($words)/100);
				if($match >= $percentage) {
					return $action($match, $this);
				}
			}
		}
		return false;
    }
	
    // Действие, выполняемое по умолчанию (при отсутствии других действий)
    public function bind_default_action($action) {
        if(empty($this->response['response']['text'])) {
            return $action($this);
        }
		return false;
    }
	
    // Добавить кнопку
    public function add_button($title, $url=NULL, $payload=NULL, $hide=FALSE) {
		if(!empty($title)) {
			$button = array(
				'title' => mb_strimwidth($title, 0, 64),
				'hide' => $hide
			);
			if(!is_null($url)) {
				$button['url'] = substr($url, 0, 1024);
			}
			if(!is_null($payload)) {
				$button['payload'] = json_encode($payload);
			}
			$this->response['response']['buttons'][] = $button;
			return true;
		}
		return false;
    }
	
	// Добавить сообщение в список случайных ответов
	public function add_message($message, $tts=null) {
		if(!empty($message)) {
			$this->response['response']['text'][] = $message;
			if(is_null($tts)) {
				$this->response['response']['tts'][] = $message;
			} else {
				$this->response['response']['tts'][] = $tts;
			}
			return true;
		}
		return false;
	}
	
	// Завершить сессию
	public function end_session() {
		$this->response['response']['end_session'] = true;
		return true;
	}
	
	// Отправляем ответ пользователю
	public function finish($die=false) {
		if(!empty($this->response['response']['text'])) {
			// Выбираем случайную фразу из всего набора
			$random = rand(0, count($this->response['response']['text'])-1);
			$this->response['response']['text'] = $this->response['response']['text'][$random];
			$this->response['response']['tts'] = $this->response['response']['tts'][$random];
			// Обрабатываем теги [word1|word2...]
			$replace_preg = '/\[(.+?)\]/';
			$replace_callback = '$words = explode(\'|\', $matches[1]); return $words[array_rand($words)];';
			$this->response['response']['text'] = preg_replace_callback($replace_preg, create_function('$matches', $replace_callback), $this->response['response']['text']);
			$this->response['response']['tts'] = preg_replace_callback($replace_preg, create_function('$matches', $replace_callback), $this->response['response']['tts']);
			// Прочие действия
			$this->response['response']['text'] = strip_tags($this->response['response']['text']);
			$this->response['response']['tts'] = strip_tags($this->response['response']['tts']);
			if(mb_strlen($this->response['response']['text']) > 1024) {
				$this->response['response']['text'] = mb_strimwidth($this->response['response']['text'], 0, 1021, '...');
			}
			if(mb_strlen($this->response['response']['tts']) > 1024) {
				$this->response['response']['tts'] = mb_strimwidth($this->response['response']['tts'], 0, 1021, '...');
			}
		} else {
			$error = 'Error: text is empty!';
			$this->response['response']['text'] = $error;
			$this->response['response']['tts'] = $error;
		}
		header('Content-Type: application/json');
		echo json_encode($this->response);
		if($die) exit;
		return !isset($error);
	}
	
}


?>