<?php

/*
	PHP библиотека для разработки навыков Яндекс.Алисы
	Author: Sergey Avdeev <thesoultaker48@gmail.com>
	URL: https://github.com/thesoultaker48/yandex-dialogs-php
*/


class YandexDialog {
	
	public $request = null;
	public $response = null;
	
	private $yametrika = null;
	private $chatbase = null;
	private $cb_handled = false;

	private $users_dir = 'users';

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
	
	// Получение части текста на основе шаблона
	public function get_some_text($patterns, $text=null) {
		if(!is_array($patterns)) $patterns = array($patterns);
		foreach($patterns as $pattern) {
			$pattern = preg_quote($pattern);
			$text = $text ?? $this->request['request']['command'];
			if(preg_match_all('/\\\{([0-9a-z_\\\:]+)\\\}/', $pattern, $matches)) {
				$m_names = array();
				for($i=0;$i<count($matches[0]);$i++) {
					$match = explode('\:', $matches[1][$i], 2);
					$m_names[] = $match[0];
					$m_type = $match[1];
					switch($m_type) {
						case 'int':
							$m_pattern = '(\d+)';
							break;
						case 'word':
							$m_pattern = '(\S+)(?:.*)';
							break;
						default:
							$m_pattern = '(.+)';
					}
					$pattern = str_replace($matches[0][$i], $m_pattern, $pattern);
				}
				$pattern = str_replace('\{\*\}', '(?:.*)', $pattern);
				if(preg_match_all('/'.$pattern.'/ui', $text, $matches)) {
					$matches = array_slice($matches, 1);
					$results = array();
					foreach($m_names as $i => $name) {
						$results[$name] = $matches[$i][0];
					}
					return $results;
				}
			}
		}
		return false;
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
    public function add_button($title, $url=null, $payload=null, $hide=false) {
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

	// Использовать Яндекс.Метрику
	public function use_yametrika($counter_id) {
		include_once 'libraries/yametrika.php';
		$this->yametrika = new YaMetrika($counter_id);
	}
	
	// Получение параметров визита
	private function yametrika_params() {
		return array(
			'user_id' => $this->request['session']['user_id'],
			'message_id' => $this->request['session']['message_id'],
			'session_id' => $this->request['session']['session_id'],
		);
	}
	
	// Передача информации о достижении цели
	public function yametrika_rgoal($target) {
		if($this->yametrika) {
			$params = $this->yametrika_params();
			return $this->yametrika->reachGoal($target, $params);
		}
		return false;
	}

	// Использовать Google Chatbase
	public function use_chatbase($api_key) {
		include_once 'libraries/chatbase.php';
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
	public function finish($die=false, $return=false) {
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
		// Яндекс.Метрика
		if($this->yametrika) {
			if(!$this->is_ping()) {
				$yametrika_session = $this->get_session_data('__yametrika');
				function crc8($data) {
					$crc8_table = array(
						0x00, 0x3e, 0x7c, 0x42, 0xf8, 0xc6, 0x84, 0xba, 0x95, 0xab, 0xe9, 0xd7,
						0x6d, 0x53, 0x11, 0x2f, 0x4f, 0x71, 0x33, 0x0d, 0xb7, 0x89, 0xcb, 0xf5,
						0xda, 0xe4, 0xa6, 0x98, 0x22, 0x1c, 0x5e, 0x60, 0x9e, 0xa0, 0xe2, 0xdc,
						0x66, 0x58, 0x1a, 0x24, 0x0b, 0x35, 0x77, 0x49, 0xf3, 0xcd, 0x8f, 0xb1,
						0xd1, 0xef, 0xad, 0x93, 0x29, 0x17, 0x55, 0x6b, 0x44, 0x7a, 0x38, 0x06,
						0xbc, 0x82, 0xc0, 0xfe, 0x59, 0x67, 0x25, 0x1b, 0xa1, 0x9f, 0xdd, 0xe3,
						0xcc, 0xf2, 0xb0, 0x8e, 0x34, 0x0a, 0x48, 0x76, 0x16, 0x28, 0x6a, 0x54,
						0xee, 0xd0, 0x92, 0xac, 0x83, 0xbd, 0xff, 0xc1, 0x7b, 0x45, 0x07, 0x39,
						0xc7, 0xf9, 0xbb, 0x85, 0x3f, 0x01, 0x43, 0x7d, 0x52, 0x6c, 0x2e, 0x10,
						0xaa, 0x94, 0xd6, 0xe8, 0x88, 0xb6, 0xf4, 0xca, 0x70, 0x4e, 0x0c, 0x32,
						0x1d, 0x23, 0x61, 0x5f, 0xe5, 0xdb, 0x99, 0xa7, 0xb2, 0x8c, 0xce, 0xf0,
						0x4a, 0x74, 0x36, 0x08, 0x27, 0x19, 0x5b, 0x65, 0xdf, 0xe1, 0xa3, 0x9d,
						0xfd, 0xc3, 0x81, 0xbf, 0x05, 0x3b, 0x79, 0x47, 0x68, 0x56, 0x14, 0x2a,
						0x90, 0xae, 0xec, 0xd2, 0x2c, 0x12, 0x50, 0x6e, 0xd4, 0xea, 0xa8, 0x96,
						0xb9, 0x87, 0xc5, 0xfb, 0x41, 0x7f, 0x3d, 0x03, 0x63, 0x5d, 0x1f, 0x21,
						0x9b, 0xa5, 0xe7, 0xd9, 0xf6, 0xc8, 0x8a, 0xb4, 0x0e, 0x30, 0x72, 0x4c,
						0xeb, 0xd5, 0x97, 0xa9, 0x13, 0x2d, 0x6f, 0x51, 0x7e, 0x40, 0x02, 0x3c,
						0x86, 0xb8, 0xfa, 0xc4, 0xa4, 0x9a, 0xd8, 0xe6, 0x5c, 0x62, 0x20, 0x1e,
						0x31, 0x0f, 0x4d, 0x73, 0xc9, 0xf7, 0xb5, 0x8b, 0x75, 0x4b, 0x09, 0x37,
						0x8d, 0xb3, 0xf1, 0xcf, 0xe0, 0xde, 0x9c, 0xa2, 0x18, 0x26, 0x64, 0x5a,
						0x3a, 0x04, 0x46, 0x78, 0xc2, 0xfc, 0xbe, 0x80, 0xaf, 0x91, 0xd3, 0xed,
						0x57, 0x69, 0x2b, 0x15
					);
					$crc = 0xff;
					for($i=0;$i<strlen($data);$i++) {
						$crc = $crc8_table[($crc ^ ord($data[$i]))];
					}
					return $crc ^ 0xff;
				}
				$fake_ip = array(
					crc8(substr($this->request['session']['user_id'], 0, 16)),
					crc8(substr($this->request['session']['user_id'], 16, 16)),
					crc8(substr($this->request['session']['user_id'], 32, 16)),
					crc8(substr($this->request['session']['user_id'], 48, 16))
				);
				$this->yametrika->userIP = implode('.', $fake_ip);
				$this->yametrika->userAgent = $this->request['meta']['client_id'];
				$url = 'alice://'.$this->request['request']['command'].'/'.substr($this->response['response']['text'], 0, 64);
				$params = $this->yametrika_params();
				$this->yametrika->hit($url, $this->request['session']['skill_id'], $yametrika['referer'], $params);
				$yametrika['referer'] = $url;
				$this->set_session_data('__yametrika', $yametrika);
			}
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
		$json = json_encode($this->response);
		if($return) {
			return $json;
		} else {
			header('Content-Type: application/json');
			echo $json;
			if($die) exit;
			return !isset($error);
		}
	}
	
}
