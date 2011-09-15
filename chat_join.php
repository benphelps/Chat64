<?php
require_once("core/core.class.php");
$poll    = new PollingChat;
$session = $_GET['session'];
$client  = 2;
$status  = true;//$poll->has_expired($session, $client);
if (!$status) {
	header("Location: /chat_expire");
	die();
}
?>
<?php include("header.php"); ?>
			<p>
				You have been invited to join a history free live chat session.  Supply a nickname and click Join Chat Session to start the chatting.
			</p>
			<form name="form" action="" method="post" accept-charset="utf-8">
				<table border="0" width="100%" style="margin:25px;">
					<tr>
						<td><input type="text" name="nickname" value="" placeholder="Your Nickname" id="nickname"></td>
					</tr>
					<tr>
						<td><input type="submit" name="submitapi" value="Join Chat Seesion" onclick="return popup('/chat2/<?=$session?>', $('nickname').value)"></td>
					</tr>
				</table>
			</form>
<?php include("footer.php"); ?>