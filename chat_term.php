<?php
require("core/core.class.php");
$poll    = new PollingChat;
$session = $_GET['session'];
$client  = $_GET['client'];
$poll->post_quit($session, $client);
?>
<script type="text/javascript" charset="utf-8">
	self.close();
</script>