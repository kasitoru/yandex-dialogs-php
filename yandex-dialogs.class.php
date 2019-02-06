<?php

/*
	PHP библиотека для разработки навыков Яндекс.Алисы
	Author: Sergey Avdeev <thesoultaker48@gmail.com>
	URL: https://github.com/thesoultaker48/yandex-dialogs-php
*/


include_once 'libraries/chatbase.php';

class YandexDialog {
	
	public $request = null;
	public $response = null;
	
	private $chatbase = null;
	private $cb_handled = false;

	private $users_dir = 'users';
	private $sessions_dir = 'sessions';

	// Конструктор
	public function __construct($version='1.0') {
		// Подготавливаем тело ответа
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
	
	// Проверка запроса на признак служебного (ping, test и т.д.)
	private function is_ping() {
		if(isset($this->request['version'])) {
			if($this->request['session']['new']) {
				$pings = array(
					'ping' => 'pong',
					'test' => 'ok',
				);
				if(array_key_exists($this->request['request']['original_utterance'], $pings)) {
					return $pings[$this->request['request']['original_utterance']];
				}
			}
		}
		return false;
	}
	
	// Получаем данные от пользователя
	public function get_request($data=null) {
		if(is_null($data)) {
			$this->request = json_decode(trim(file_get_contents('php://input')), true);
		} else {
			$this->request = $data;
		}
		if(isset($this->request['version'])) {
			session_id($this->request['session']['session_id']);
			session_start();
			$this->response['session']['session_id'] = $this->request['session']['session_id'];
			$this->response['session']['message_id'] = $this->request['session']['message_id'];
			$this->response['session']['user_id'] = $this->request['session']['user_id'];
			if($answer = $this->is_ping()) {
				$this->add_message($answer);
				$this->finish(true);
			}
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
	
	// Действие, выполняемое при удовлетворении процентной схожести предложения
    public function bind_suggestion_action($text, $percentage, $action) {
		$text = mb_strtolower($text);
		// fixme: учитывать слова с дефисом ("по-русски", "юго-запад" и т.д.)
		if(preg_match_all('/([0-9a-zа-яё]+)/u', $text, $words)) {
			return bind_percentage_action($words, $percentage, $action);
		} else {
			return false;
		}
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

	// Получение данных пользователя
	public function get_user_data($name) {
		$file = $this->users_dir.'/'.md5($this->request['session']['user_id']).'.dat';
		if(file_exists($file)) {
			$data = file_get_contents($file);
			$user = unserialize($data);
			if(isset($user[$name])) {
				return $user[$name];
			}
		}
		return false;
	}

	// Сохранение данных пользователя
	public function set_user_data($name, $value) {
		$file = $this->users_dir.'/'.md5($this->request['session']['user_id']).'.dat';
		if(file_exists($file)) {
			$data = file_get_contents($file);
			$user = unserialize($data);
		} else {
			$user = array();
		}
		if(is_null($value)) {
			unset($user[$name]);
		} else {
			$user[$name] = $value;
		}
		if(count($user)) {
			if(!is_dir($this->users_dir)) {
				mkdir($this->users_dir);
			}
			$data = serialize($user);
			return (bool)file_put_contents($file, $data);
		} elseif(file_exists($file)) {
			return unlink($file);
		} else {
			return true;
		}
	}

	// Получение данных сессии
	public function get_session_data($name) {
		if(isset($_SESSION[$name])) {
			return $_SESSION[$name];
		} else {
			return false;
		}
	}

	// Сохранение данных сессии
	public function set_session_data($name, $value) {
		if(is_null($value)) {
			unset($_SESSION[$name]);
		} else {
			$_SESSION[$name] = $value;
		}
		return true;
	}
	
	// Завершить сессию
	public function end_session() {
		$this->response['response']['end_session'] = true;
		return true;
	}
	
	// Использовать Google Chatbase
	public function use_chatbase($api_key) {
		$this->chatbase = new ChatbaseAPI\Chatbase($api_key);
	}
	
	// Установить значение флага "handled"
	public function chatbase_handled($handled=true) {
		if($this->chatbase) {
			$this->cb_handled = $handled;
			return true;
		}
		return false;
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
		// Google Chatbase
		if($this->chatbase) {
			if(!$this->is_ping()) {
				$chatbase = $this->chatbase->twoWayMessages(
					$this->request['session']['user_id'],
					$this->request['session']['skill_id'],
					$this->request['request']['command'],
					$this->response['response']['text'],
					$this->request['meta']['client_id'],
					$this->request['session']['session_id'],
					$this->request['version'],
					$this->response['version'],
					$this->cb_handled
				);
				$this->chatbase->sendall($chatbase);
			}
		}
		// Уничтожение сессии
		if($this->response['response']['end_session']) {
			session_destroy();
		}
		// Выводим результат
		header('Content-Type: application/json');
		echo json_encode($this->response);
		if($die) exit;
		return !isset($error);
	}
	
}


?>
