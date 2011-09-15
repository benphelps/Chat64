<?php

require_once("core/core.class.php");
$poll    = new PollingChat;
$session_id = $_GET['session'];
$client  = $_GET['client'];
$nick    = $_GET['name'];
$status  = $poll->session_create($session_id, $client, $nick);

if ($client == 2) {
	$session_master = $poll->poll('name', $session_id, 2);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Live Chat Session</title>
		<link href="http://fonts.googleapis.com/css?family=Droid+Sans:400,700" rel='stylesheet' type='text/css'>
		<link href="/resources/style.css" rel='stylesheet' type='text/css'>
		<script src="/resources/poll.js" type="text/javascript" charset="utf-8"></script>
	</head>
	<body onload="chat_start('<?=$session_id?>', <?=$client?>, '<?=$nick?>')">