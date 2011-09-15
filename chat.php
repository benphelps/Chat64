<?php $client = $_GET['client']; ?>
<?php include("chat_header.php"); ?>
		<div id="link"><?php if ($client==1): ?><strong>Share This Link:</strong> <input type="text" name="link" value="http://chat.64bits.co/<?=$session_id?>" readonly onclick="select()"><?php else: ?>You are chatting with: <strong><?=$session_master?></strong><?php endif ?></div>
		<div id="chatbox"><form name="form"><input autocomplete="off" type="text" name="message" value="" id="message" onkeypress="return chat_keys(event, '<?=$session_id?>', <?=$client?>)"></form>
			<div id="close"><span id="active">&nbsp;</span><a href="/chat_term<?=$client?>/<?=$session_id?>">Close this chat session.</a></div>
		</div>
		<div id="chat"></div>
<?php include("chat_footer.php"); ?>
