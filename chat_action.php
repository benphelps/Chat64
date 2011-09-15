<?php

require("core/core.class.php");

$poll = new PollingChat;
$poll->timeout = 30;

// polling query, actions are sent to the browser from here
if (isset($_POST['poll'])) {
	$time       = time();
	$session_id = $_POST['session'];
	$client_id  = $_POST['client'];
	$response = false;
	while (time() - $time < $poll->timeout) {
		$message = $poll->poll('message', $session_id, $client_id);
		if ($message) {
			switch ($message) {
				case "::JOIN::":
					$response = json_encode(array(
						"status" => "join",
						"name" => $poll->poll('name', $session_id, $client_id),
						"message" => "",
						"time" => time()
					));
				break 2;
				
				case "::QUIT::":
					$response = json_encode(array(
						"status" => "quit",
						"name" => $poll->poll('name', $session_id, $client_id),
						"message" => "",
						"time" => time()
					));
				break 2;
				
				default:
					$response = json_encode(array(
						"status" => "success",
						"name" => $poll->poll('name', $session_id, $client_id),
						"message" => $message,
						"time" => time()
					));
				break 2;
			}
		}
		usleep(25000);
	}
	if (!$response) {
		$response = json_encode(array(
			"status" => "timeout"
		));
	}
	echo trim($response);
}

// posting query, actions are sent to the server here
if (isset($_POST['message'])) {
	$session_id = $_POST['session'];
	$client_id  = $_POST['client'];
	$message    = $_POST['message'];
	$status     = $poll->push_message($message, $session_id, $client_id);
	$response = json_encode(array(
		"status" => ($status?"success":"error")
	));
	echo trim($response);
}

if (isset($_POST['activity'])) {
	$session_id = $_POST['session'];
	$client_id  = $_POST['client'];
	$status     = $poll->push_activity($session_id, $client_id);
	$response = json_encode(array(
		"status" => ($status?"success":"error")
	));
	echo trim($response);
}

// polling query, actions are sent to the browser from here
if (isset($_POST['status'])) {
	$time       = time();
	$session_id = $_POST['session'];
	$client_id  = $_POST['client'];
	$response   = false;
	while (time() - $time < $poll->timeout) {
		$status = $poll->poll('status', $session_id, $client_id);
		if ($status > time()-3) {
			$response = json_encode(array(
				"active" => true,
				"name" => $poll->poll('name', $session_id, $client_id),
			));
			break;
		}
		usleep(25000);
	}
	if (!$response) {
		$response = json_encode(array(
			"status" => "timeout"
		));
	}
	echo trim($response);
}

?>