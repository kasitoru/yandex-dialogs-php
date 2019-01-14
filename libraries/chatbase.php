<?php

namespace ChatbaseAPI;

/**
* Class containing helper functions to format messages and
* send POST to API end point
*/
class Chatbase {

	/**
	* @var $API_KEY API Key provided by Chatbase
	*/
	private $API_KEY = null;

	/**
	* @var $API_MULTI_URL API endpoint to POST multiple messages
	*/
	private $API_MULTI_URL = "https://chatbase.com/api/messages";

	/**
	* @var $API_URL API endpoint to POST single message
	*/
	private $API_URL = "https://chatbase.com/api/message";

	/**
	* Constructor
	*
	* @param $apikey - Your chatbase agent API key
	*/
	function __construct($apikey) {
		$this->API_KEY = $apikey;
	}

	/**
	* @method postRequest
	* Method that encodes array into JSON string, sends POST request to API endpoint, and
	* returns JSON decoded object.
	*
	* @param String $url - URL to API endpoint
	* @param String $data - Array containing respective endpoint parameters
	* @return Object - Json decoded object
	*/
	private function postRequest($url, $data) {
		$jsonencodedData = json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonencodedData);
		$result = curl_exec($ch);
		return json_decode($result);
	}

	/**
	* @method getMilliseconds
	* MEthod that returns time in milliseconds.
	* @return float - Time in milliseconds
	*/
	private function getMilliseconds() {
		$microtime = round(microtime(true) * 1000);
		return number_format($microtime, 0, ".", "");
	}

	/**
	* @method send
	* Helper method to send single message to API endpoint
	*
	* @param Array $request_data - Array containing parameters required by API endpoint (message)
	*/
	public function send($request_data) {
		return $this->postRequest($this->API_URL, $request_data);
	}

	/**
	* @method sendAll
	* Helper method to send multiple messages to API endpoint
	*
	* @param Array $request_data - Array containing parameters required by API endpoint (messages)
	*/
	public function sendAll($request_data) {
		return $this->postRequest($this->API_MULTI_URL, $request_data);
	}

	/**
	* @method userMessage
	* Method that returns user message type array containing parameters required by endpoint.
	*
	* @param $user_id     - User Identifier
	* @param $platform    - Platform like, facebook, slack, alexa etc
	* @param $message     - Message sent by user
	* @param $intent      - Intent classifying message
	* @param $session_id    - Used to define your own custom sessions
	* @param $not_handled - (boolean) If handled by agent or not
	* @param $feedback    - (boolean) If feedback to agent or not
	* @return Array $request_data - Array containing parameters required by API endpoint
	*/
	public function userMessage($user_id, $platform, $message = "", $intent = "", $session_id = "", $version = "", $not_handled = false, $feedback = false) {
		$request_data = array(
			'api_key' => $this->API_KEY,
			'type' => 'user',
			'user_id' => $user_id,
			'time_stamp' => $this->getMilliseconds(),
			'platform' => $platform,
			'message' => $message,
			'intent' => $intent,
			'session_id' => $session_id,
			'version' => $version,
			'not_handled' => $not_handled,
			'feedback' => $feedback
		);
		return $request_data;
	}

	/**
	* @method agentMessage
	* Method that returns agent message type array containing parameters required by endpoint.
	*
	* @param $user_id     - User Identifier
	* @param $platform    - Platform like, facebook, slack, alexa etc
	* @param $message     - Message sent by agent
	* @param $intent      - Intent classifying message
	* @param $session_id    - Used to define your own custom sessions
	* @param $not_handled - (boolean) If handled by agent or not
	* @return Array $request_data - Array containing parameters required by API endpoint
	*/
	public function agentMessage($user_id, $platform, $message = "", $intent = "", $session_id = "", $version = "", $not_handled = false) {
		$request_data = array(
			'api_key' => $this->API_KEY,
			'type' => 'agent',
			'user_id' => $user_id,
			'time_stamp' => $this->getMilliseconds(),
			'platform' => $platform,
			'message' => $message,
			'session_id' => $session_id,
			'version' => $version,
			'not_handled' => $not_handled
		);
		return $request_data;
	}

	/**
	* @method twoWayMessages
	* Method that returns user message type combined with agent message type array
	* containing parameters required by endpoint.
	*
	* @param $user_id       - User Identifier
	* @param $platform      - Platform like, facebook, slack, alexa etc
	* @param $user_message  - Message sent by user
	* @param $agent_message - Message sent by agent
	* @param $intent        - Intent classifying message
	* @param $session_id    - Used to define your own custom sessions
	* @param $not_handled   - (boolean) If handled by agent or not
	* @return Array $request_data - Array containing array of messages required by API endpoint (messages)
	*/
	public function twoWayMessages($user_id, $platform, $user_message = "", $agent_message = "", $intent = "", $session_id = "", $user_version = "", $agent_version = "", $not_handled = false) {
		$user_data = array(
			'type' => 'user',
			'user_id' => $user_id,
			'platform' => $platform,
			'message' => $user_message,
			'intent' => $intent,
			'session_id' => $session_id,
			'version' => $user_version,
			'not_handled' => $not_handled,
			'time_stamp' => $this->getMilliseconds(),
		);
		$agent_data = array(
			'type' => 'agent',
			'user_id' => $user_id,
			'platform' => $platform,
			'message' => $agent_message,
			'session_id' => $session_id,
			'version' => $agent_version,
			'not_handled' => $not_handled,
			'time_stamp' => $this->getMilliseconds(),
		);
		return $this->rawMultipleMessages(array($user_data, $agent_data));
	}

	/**
	* @method rawMultipleMessages
	* A helper method that appends api_key and time_stamp to each message and
	* format result as array of messages.
	*
	* @param Array $arr_data - Array containing array of messages
	* @return Array $request_data - Array containing array of messages with common data appended
	*/
	public function rawMultipleMessages($arr_data) {
		foreach ($arr_data as $key => $value) {
			$value['api_key'] = $this->API_KEY;
			$value['time_stamp'] = $this->getMilliseconds();
			$arr_data[$key] = $value;
		}
		$request_data = array(
			'messages' => $arr_data
		);
		return $request_data;
	}
}
