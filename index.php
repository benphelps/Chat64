<?
if (isset($_GET['session'])) {
	header("Location: /chat_join/".$_GET['session']);
}
include("header.php"); 
$poll = new PollingChat; 
?>
			<p>
				This is a simple history free live chat service.  Supply a nickname and then share the chat link with your friend to start a private chat session.
			</p>
			<form name="form" action="" method="post" accept-charset="utf-8">
				<table border="0" width="100%" style="margin:25px;">
					<tr>
						<td><input type="text" name="nickname" value="" placeholder="Your Nickname" id="nickname"></td>
					</tr>
					<tr>
						<td><input type="submit" name="submitapi" value="Start Private Chat" onclick="return popup('/chat1/<?=$poll::fetch_guid()?>', $('nickname').value)"></td>
					</tr>
				</table>
			</form>
			<p>
				<strong>Important:</strong> Your message is only temporarily saved in a database, once the other client receives the message, it is removed.  On average, its stored for about 100ms, however, should the client never receive the message, scheduled tasks remove messages older than 30 seconds.
			</p>
<?php include("footer.php"); ?>