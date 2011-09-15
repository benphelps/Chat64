<?php

/**
 * Polling Web Chat
 *
 * @package PollingLiveChat
 * @author Ben Phelps
 **/
class PollingChat
{
	
	/**
	 * Class Construction
	 *
	 * @return void
	 **/
	function __construct()
	{
		require("config.php");
		$this->database = new mysqli(
			$config["db"]["hostname"], 
			$config["db"]["username"], 
			$config["db"]["password"], 
			$config["db"]["database"]
		);
	}
	
	/**
	 * Class Destruction
	 *
	 * @return void
	 */
	function __destruct()
	{
		//$this->database->close();
	}
	
	/**
	 * Check for messages to be sent in the database
	 *
	 * @param string $session Session ID
	 * @param int $client Client ID - 1|2
	 * @return string
	 */
	private function fetch_message($session, $client)
	{
		$sql = $this->database->prepare("SELECT message FROM history WHERE session = ? AND client = ? ORDER BY stamp ASC LIMIT 1");
		$sql->bind_param("si", $session, $this->switch_client($client));
		$sql->execute();
		$sql->bind_result($message);
		$sql->fetch();
		$sql->store_result();
		$sql->free_result();
		$sql->close();
		
		$result = (!empty($message)?$message:false);
		
		if ($result) {
			$sql_delete = $this->database->prepare("DELETE FROM history WHERE session = ? AND client = ? ORDER BY stamp ASC LIMIT 1");
			$sql_delete->bind_param("si", $session, $this->switch_client($client));
			$sql_delete->execute();
			$sql_delete->close();
		}
		
		return $result;
	}
	
	/**
	 * Fetch a sessions name based on client
	 *
	 * @param string $session Session ID
	 * @param int $client Requesting Client ID
	 * @return string
	 */
	private function fetch_name($session, $client)
	{
		$sql = $this->database->prepare("SELECT name FROM session WHERE session_id = ? AND client = ?");
		$sql->bind_param("si", $session, $this->switch_client($client));
		$sql->execute();
		$sql->bind_result($nickname);
		$sql->fetch();
		return (!empty($nickname)?$nickname:"Anonymous");
	}
	
	/**
	 * Fetch a sessions activity based on client
	 *
	 * @param string $session Session ID
	 * @param int $client Requesting Client ID
	 * @return int
	 */
	private function fetch_activity($session, $client)
	{
		$sql = $this->database->prepare("SELECT stamp FROM activity WHERE session = ? AND client = ?");
		$sql->bind_param("si", $session, $this->switch_client($client));
		$sql->execute();
		$sql->bind_result($stamp);
		$sql->fetch();
		$sql->close();
		return (!empty($stamp)?$stamp:false);
	}
	
	/**
	 * Polling query system.
	 *
	 * Used to poll the database for info, based on action.  These are the most
	 * frequently queried actions.
	 *
	 * @param string $action Polling action
	 * @param string $session Session ID
	 * @param string $client Requesting Client
	 * @return mixed
	 */
	public function poll($action, $session, $client)
	{
		switch ($action) {
			case 'message':
				return $this->fetch_message($session, $client);
			break;
			
			case 'nickname':
			case 'name':
				return $this->fetch_name($session, $client);
			break;
			
			case 'status':
				return $this->fetch_activity($session, $client);
			break;
			
			default:
				// nothing
			break;
		}
		
	}
	
	/**
	 * Pushes a client message to the database
	 *
	 * @param string $message Message String
	 * @param string $session Session ID
	 * @param int $client Client ID
	 * @return bool
	 */
	public function push_message($message, $session, $client)
	{
		$sql = $this->database->prepare("INSERT INTO history (`session`, `client`, `message`, `stamp`) VALUES(?, ?, ?, ?)");
		$sql->bind_param("sisi", $session, $client, $message, time());
		$sql->execute();
		if ($sql->errno == 0) {
			$sql->close();
			return true;
		}
		$sql->close();
		return false;
	}
	
	/**
	 * Updates client activity status
	 *
	 * @param string $session Session ID
	 * @param int $client Client ID
	 * @return bool
	 */
	public function push_activity($session, $client)
	{
		$sql = $this->database->prepare("SELECT stamp FROM activity WHERE session = ? AND client = ?");
		$sql->bind_param("si", $session, $client);
		$sql->execute();
		$sql->store_result();
		$count = $sql->num_rows;
		$sql->close();
		if ($count == 0) {
			$sql_insert = $this->database->prepare("INSERT INTO activity (session, stamp, client) VALUES (?, ?, ?)");
			$sql_insert->bind_param("sii", $session, time(), $client);
			$sql_insert->execute();
			$sql_insert->close();
		}
		else {
			$sql_update = $this->database->prepare("UPDATE activity SET stamp = ? WHERE session = ? AND client = ?");
			$sql_update->bind_param("isi", time(), $session, $client);
			$sql_update->execute();
			$sql_update->close();
		}
		return true;
	}
	
	/**
	 * Used to check if a chat session invite link has expired
	 *
	 * @param string $session Session ID
	 * @param int $client Requesting Client ID
	 * @return bool
	 */
	public function has_expired($session, $client)
	{
		$sql = $this->database->prepare("SELECT id FROM session WHERE session_id = ? AND client = ?");
		$sql->bind_param("si", $session, $client);
		$sql->execute();
		$sql->store_result();
		$count = $sql->num_rows;
		$sql->close();
		return $count == 0;
	}
	
	/**
	 * Used to expire a chat session.  Not used currently.
	 *
	 * @param string $session Session ID
	 * @return bool
	 */
	public function expire_session($session)
	{
		$sql = $this->database->prepare("DELETE FROM session WHERE session_id = ?");
		$sql->bind_param("s", $session);
		$sql->execute();
		$sql->close();
		return true;
	}
	
	/**
	 * Publish a client quit message.
	 *
	 * @param string $session Session ID
	 * @param int $client Client ID
	 * @return bool
	 */
	public function post_quit($session, $client)
	{
		$quit_message = "::QUIT::";
		$quit_time = time();
		$sql = $this->database->prepare("INSERT INTO history (session, message, client, stamp) VALUES(?, ?, ?, ?)");
		$sql->bind_param("ssii", $session, $quit_message, $client, $quit_time);
		$sql->execute();
		$sql->close();
		return true;
	}
	
	/**
	 * Create a chat session
	 *
	 * @param string $session Session ID
	 * @param int $client Client ID
	 * @param string $nickname Client Name
	 * @return bool
	 */
	public function session_create($session, $client, $nickname)
	{
		$time = time();
		$sql = $this->database->prepare("INSERT IGNORE INTO session (name, client, session_id, creation) VALUES(?, ?, ?, ?)");
		$sql->bind_param("sisi", $nickname, $client, $session, $time);
		$sql->execute();
		$sql->close();
		return true;
	}
	
	/**
	 * Used to switch the requesting client ID around.
	 *
	 * @param string $client client ID
	 * @return int
	 */
	public function switch_client($client)
	{
		return ($client==1?2:1);
	}
	
	/**
	 * Generates a "unique" ID.
	 *
	 * @param int $len Length of requested string, max of ~40
	 * @return string
	 */
	static function fetch_guid($len = 5)
	{
		return substr(sha1(rand()), 0, $len);
	}
	
	
} // END class PollingChat


?>