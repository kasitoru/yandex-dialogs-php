<?php

/*
	PHP библиотека для разработки навыков Яндекс.Алисы
	Author: Sergey Avdeev <thesoultaker48@gmail.com>
	URL: https://github.com/thesoultaker48/yandex-dialogs-php
*/

namespace YandexStation;

class YandexDialog {

	public $request = null;
	public $response = null;
	public $users_dir = 'users';
	public $sentences = false;

	private $debug = null;
	private $yametrika = null;
	private $chatbase = null;
	private $cb_handled = false;

	// Конструктор
	public function __construct($version='1.0') {
		// Подготавливаем тело ответа
		$this->response = [
			'response' => [
				'text' => null,
				'tts' => null,
				'buttons' => [],
				'end_session' => false
			],
			'session' => [
				'session_id' => null,
				'message_id' => null,
				'user_id' => null
			],
			'version' => $version
		];
	}
	
	// Проверка запроса на признак служебного (ping, test и т.д.)
	private function is_ping() {
		if(isset($this->request['version'])) {
			if($this->request['session']['new']) {
				$pings = [
					'ping' => 'pong',
					'test' => 'ok',
				];
				if(array_key_exists($this->request['request']['original_utterance'], $pings)) {
					return $pings[$this->request['request']['original_utterance']];
				}
			}
		}
		return false;
	}

	// Включить отладку
	public function debug() {
		$this->debug = microtime(true);
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
		if(!is_array($patterns)) $patterns = [$patterns];
		foreach($patterns as $pattern) {
			$pattern = preg_quote($pattern);
			$text = $text ?? $this->request['request']['command'];
			if(preg_match_all('/\\\{([0-9a-z_\\\:]+)\\\}/', $pattern, $matches)) {
				$m_names = [];
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
					$results = [];
					foreach($m_names as $i => $name) {
						$results[$name] = $matches[$i][0];
					}
					return $results;
				}
			}
		}
		return false;
	}
	
	// Разбивает предложение на массив слов
	public function get_sentence_words($text) {
		// fixme: учитывать слова с дефисом ("по-русски", "юго-запад" и т.д.)
		$text = mb_strtolower($text);
		if(preg_match_all('/([0-9a-zа-яё]+)/u', $text, $words)) {
			return $words[1];
		}
		return false;
	}
		
	// Получить процентное содержание слов в массиве
	public function words_percentage($words, $tokens) {
		$matches = 0;
		foreach($words as $word) {
			foreach($tokens as $token) {
				if(is_array($word)) {
					foreach($word as $item) {
						if($this->compare_words($token, $item)) {
							$matches++;
							break 2;
						}
					}
				} else {
					if($this->compare_words($token, $word)) {
						$matches++;
						break;
					}
				}
			}
		}
		return $matches/(count($words)/100);
	}

	// Сравнение двух слов на схожесть
	public function compare_words($first, $second) {
		return strcmp(mb_strtolower($first), mb_strtolower($second)) == 0;
	}
	
	// Определение процента схожести двух предложений
	public function compare_sentences($first, $second) {
		if($this->sentences) {
			// fixme: учитывать слова с дефисом ("по-русски", "юго-запад" и т.д.)
			$first = preg_replace('/([^\s0-9a-zа-яё]+)/ui', '', $first);
			$first = preg_replace('/\s{2,}/', ' ', $first);
			// fixme: учитывать слова с дефисом ("по-русски", "юго-запад" и т.д.)
			$second = preg_replace('/([^\s0-9a-zа-яё]+)/ui', '', $second);
			$second = preg_replace('/\s{2,}/', ' ', $second);
			return strcmp(trim($first), trim($second)) == 0;
		} else {
			$first = $this->get_sentence_words($first);
			$second = $this->get_sentence_words($second);
			if(count($first) < count($second)) {
				return $this->words_percentage($second, $first);
			} else {
				return $this->words_percentage($first, $second);
			}
		}
	}

	// Проверка признака старта новой сессии
	public function is_new_session() {
		return $this->request['session']['new'];
	}
	
	// Проверка запуска с помощью "Алиса попроси/скажи..."
	public function is_cmd_start() {
		return $this->is_new_session() && !empty($this->request['request']['command']);
	}
	
	// Действие, выполняемое при старте новой сессии
	public function bind_new_action($action) {
		if(empty($this->response['response']['text'])) {
			if($this->is_new_session()) {
				$action($this);
				return true;
			}
		}
		return false;
	}
	
    // Действие, выполняемое при наличии определенных слов
    public function bind_words_action($words, $action) {
		if(empty($this->response['response']['text'])) {
			$tokens = [];
			foreach($words as $word) {
				foreach($this->request['request']['nlu']['tokens'] as $token) {
					if($this->compare_words($word, $token)) {
						$tokens[] = mb_strtolower($token);
					}
				}
			}
			if(count($tokens)>0) {
				$tokens = array_unique($tokens, SORT_STRING);
				return $action($tokens, $this);
			}
		}
		return false;
    }

	// Действие, выполняемое при удовлетворении процентного содержания определенных слов
    public function bind_percentage_action($words, $percentage, $action) {
		if(empty($this->response['response']['text'])) {
			if($tokens = $this->request['request']['nlu']['tokens']) {
				$match = $this->words_percentage($words, $tokens);
				if($match >= $percentage) {
					return $action($match, $this);
				}
			}
		}
		return false;
    }

	// Действие, выполняемое при удовлетворении процентной схожести предложения
    public function bind_sentence_action($text, $percentage, $action) {
		if(empty($this->response['response']['text'])) {
			if($this->sentences) {
				$match = $this->compare_sentences($text, $this->request['request']['command']);
				if($match >= $percentage) {
					return $action($match, $this);
				}
			} else {
				if($words = $this->get_sentence_words($text)) {
					return $this->bind_percentage_action($words, $percentage, $action);
				} else {
					return false;
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
    public function add_button($title, $url=null, $payload=null, $hide=false) {
		if(!empty($title)) {
			$button = [
				'title' => mb_strimwidth($title, 0, 64),
				'hide' => $hide
			];
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
			$user = [];
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
		$this->yametrika = new \ServerYaMetrika\YaMetrika($counter_id);
	}
	
	// Получение параметров визита
	private function yametrika_params() {
		return [
			'user_id' => $this->request['session']['user_id'],
			'message_id' => $this->request['session']['message_id'],
			'session_id' => $this->request['session']['session_id'],
		];
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
		$this->chatbase = new \ChatbaseAPI\Chatbase($api_key);
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
	public function finish($return=false) {
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
				$__yametrika = $this->get_session_data('__yametrika');
				$crc8 = new \PBurggraf\CRC\CRC8\CRC8();
				$fake_ip = [
					$crc8->calculate(substr($this->request['session']['user_id'], 0, 16)),
					$crc8->calculate(substr($this->request['session']['user_id'], 16, 16)),
					$crc8->calculate(substr($this->request['session']['user_id'], 32, 16)),
					$crc8->calculate(substr($this->request['session']['user_id'], 48, 16))
				];
				$this->yametrika->userIP = implode('.', $fake_ip);
				$this->yametrika->userAgent = $this->request['meta']['client_id'];
				$url = 'alice://'.$this->request['request']['command'].'/'.substr($this->response['response']['text'], 0, 64);
				$params = $this->yametrika_params();
				$this->yametrika->hit($url, $this->request['session']['skill_id'], $__yametrika['referer'], $params);
				$__yametrika['referer'] = $url;
				$this->set_session_data('__yametrika', $__yametrika);
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
		// Отладочная информация
		if(!is_null($this->debug)) {
			$this->response['debug'] = [
				'memory' => memory_get_peak_usage(),
				'php' => phpversion(),
				'server_ip' => $_SERVER['SERVER_ADDR'],
				'remote_ip' => $_SERVER['REMOTE_ADDR'],
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
				'execution_time' => microtime(true) - $this->debug,
			];
		}
		// Выводим результат
		$json = json_encode($this->response);
		if($return) {
			return $json;
		} else {
			header('Content-Type: application/json');
			echo $json;
			return !isset($error);
		}
	}
	
}