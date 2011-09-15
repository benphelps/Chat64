<?php
require("core/core.class.php");
$poll = new PollingChat;


/*

	test cycle is as follows

	Client 1 sends a message
	Client 1 sends an activity heartbeat
	Client 2 checks for activity with client 1
	Client 2 receives client 1's message
	Client 2 sends a message
	Client 2 sends a activity heartbeat
	Client 1 checks for activity wth client 2
	Client 1 receives client 2's message

*/

$status = $poll->push_message("Test Message", "test_session", 1);
if (!$status) {
	echo "<strong>Client 1 send test failed!</strong>" . "<br />";
}
else {
	echo "Client 1 send test success." . "<br />";
}

$status = $poll->push_activity("test_session", 1);
if (!$status) {
	echo "<strong>Client 1 activity push test failed!</strong>" . "<br />";
}
else {
	echo "Client 1 activity push test success." . "<br />";
}

// Test Client 2 receive
$status = $poll->poll('status', "test_session", 2);
if (!$status) {
	echo "<strong>Client 2 status test failed!</strong>" . "<br />";
}
else {
	echo "Client 2 status test success." . "<br />";
}

// Test Client 2 receive
$status = $poll->poll('message', "test_session", 2);
if (!$status) {
	echo "<strong>Client 2 receive test failed!</strong>" . "<br />";
}
else {
	echo "Client 2 receive test success." . "<br />";
}

// Test Client 2 send
$status = $poll->push_message("Test Message", "test_session", 2);
if (!$status) {
	echo "<strong>Client 2 send test failed!</strong>" . "<br />";
}
else {
	echo "Client 2 send test success." . "<br />";
}

$status = $poll->push_activity("test_session", 2);
if (!$status) {
	echo "<strong>Client 2 activity push test failed!</strong>" . "<br />";
}
else {
	echo "Client 2 activity push test success." . "<br />";
}

// Test Client 2 receive
$status = $poll->poll('status', "test_session", 1);
if (!$status) {
	echo "<strong>Client 1 status test failed!</strong>" . "<br />";
}
else {
	echo "Client 1 status test success." . "<br />";
}


// Test Client 1 receive
$status = $poll->poll('message', "test_session", 1);
if (!$status) {
	echo "<strong>Client 1 receive test failed!</strong>" . "<br />";
}
else {
	echo "Client 1 receive test success." . "<br />";
}

?>