function $(a) { return document.getElementById(a) }
window.onresize = resize
var my_name = ""
function resize()
{
	$("chat").style.height = window.innerHeight-72+"px"
}
function xmlRequest( url, arg, callback )
{
	callback = callback || function(){}
	var http, self = arguments.callee
	if (window.XMLHttpRequest) {
		http = new XMLHttpRequest()
	} else if (window.ActiveXObject) {
		try {
			http = new ActiveXObject('Msxml2.XMLHTTP')
		} catch(e) {
			http = new ActiveXObject('Microsoft.XMLHTTP')
		}
	}
	http.onreadystatechange = function(){ 
		if( http.readyState == 4 ) {
			callback(this)
		} 
	}
	if (http) {
		http.open("POST", url, true)
		http.setRequestHeader("Content-type","application/x-www-form-urlencoded")
		http.send(arg)
	}
}
function smartScroll() {
	var obj = $("chat")
	if (((obj.scrollTop + obj.clientHeight + 50) <= obj.scrollHeight) == false) {
		$("chat").scrollTop = $("chat").scrollHeight
	}
}
function give_time() {
	var currentTime = new Date()
	var hours = currentTime.getHours()
	var minutes = currentTime.getMinutes()
	var suffix = "AM"
	if (hours >= 12) {
		suffix = "PM"
		hours = hours - 12
	}
	if (hours == 0) {
		hours = 12
	}
	if (minutes < 10)
	minutes = "0" + minutes
	return hours + ":" + minutes + " " + suffix
}
function tink() {
	soundManager.play('tink','/resources/tink.mp3')
}
function chat(session, client) {
	var message = $('message').value
	append_message(my_name, message, "sender")	
	clear_chat()
	send_message(message, session, client)
	return false
}

function append_message(name, message, type) {
	$("chat").innerHTML = $("chat").innerHTML + '<div class="message_line"><span class="date">'+give_time()+'</span><span class="'+type+'">'+name+'</span><span class="message">'+format(message)+'</span></div>'+"\n"
}
function append_join (name) {
	$("chat").innerHTML = $("chat").innerHTML + '<div class="message_line"><span class="join">'+name+' has joined the chat.</span></div>'+"\n"
}
function append_quit (name) {
	$("chat").innerHTML = $("chat").innerHTML + '<div class="message_line"><span class="join">'+name+' has left the chat.</span></div>'+"\n"
}
function send_message(message, session, client) {
	var post = "session="+session+"&client="+client+"&message="+escape(message)
	xmlRequest('/chat_action/post', post, function(http){
		smartScroll($('chat'))
	})
}
function poll(session, client) {
	var post = "session="+session+"&client="+client+"&poll="+Math.round(new Date().getTime() / 1000)
	xmlRequest('/chat_action/poll', post, function(http){ 
		var data = JSON.parse(http.responseText)
		
		switch(data.status) {
			
			case "success":
				append_message(data.name, data.message, "reciver")
				smartScroll()
				clear_chat()
				clear_status()
				poll(session, client)
			break;
			
			case "join":
				append_join(data.name)
				smartScroll()
				poll(session, client)
			break;
			
			case "quit":
				append_quit(data.name)
				smartScroll()
			break;
			
			case "timeout": // This isn't bad, polling requests expire after 30 seconds
				poll(session, client)
			break;
			
			default:
				console.error("Unknown Action: " + data.status)
			break
			
		}
	})
}
function activity(session, client) {
	$("active").innerHTML = '&nbsp;'
	var post = "session="+session+"&client="+client+"&status="+Math.round(new Date().getTime() / 1000)
	xmlRequest('/chat_action/status', post, function(http){
		var data = JSON.parse(http.responseText)
		if (data.active == true) {
			$("active").innerHTML = '<span id="activity_dot">&#8226;</span><span id="activity">'+data.name+' is typing...</span>'
			setTimeout('activity(\''+session+'\', '+client+')', 5000)
		}
		else {
			$("active").innerHTML = '&nbsp;'
			activity(session, client)
		}
	})
}

function chat_start(session, client, name) {
	document.form.message.focus()
	my_name = name
	resize()
	smartScroll()
	poll(session, client)
	activity(session, client)
}
var polling_key_count = 0
function chat_keys (event, session, client) {
	if ( event.keyCode == 13 ){
		return chat(session, client)
	}
	else {
		if (polling_key_count > 1) {
			var post = "session="+session+"&client="+client+"&activity="+Math.round(new Date().getTime() / 1000)
			xmlRequest('/chat_action/activity', post, function(http){})
			polling_key_count = 0
		}
		else {
			polling_key_count++
		}
	}
}
function clear_chat() {
	$("message").value = ''
}
function clear_status() {
	$("active").innerHTML = ''
}
function popup(url, temp) {
	newwindow=window.open(url+'-'+temp,'chat_'+temp,'height=400,width=500')
	if (window.focus) {
		newwindow.focus()
	}
	return false
}
function hyperlink(text) {
    var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return text.replace(exp,"<a target='_blank' href='$1'>$1</a>"); 
}
function smile(text) {
	text = text.replace(/;\)/gi  , '<img src="../resources/emoticons/Wink.png" class="emoticon" />')
	text = text.replace(/X\)~/gi , '<img src="../resources/emoticons/Facial.png" class="emoticon" />')
	text = text.replace(/:\)/gi  , '<img src="../resources/emoticons/Smile.png" class="emoticon" />')
	text = text.replace(/\(:/gi  , '<img src="../resources/emoticons/Smile.png" class="emoticon" />')
	text = text.replace(/:@/gi   , '<img src="../resources/emoticons/Angry Face.png" class="emoticon" />')
	text = text.replace(/:\[/gi  , '<img src="../resources/emoticons/Blush.png" class="emoticon" />')
	text = text.replace(/:S/gi   , '<img src="../resources/emoticons/Undecided.png" class="emoticon" />')
	text = text.replace(/:'\(/gi , '<img src="../resources/emoticons/Crying.png" class="emoticon" />')
	text = text.replace(/:\|/gi  , '<img src="../resources/emoticons/Foot In Mouth.png" class="emoticon" />')
	text = text.replace(/:\(/gi  , '<img src="../resources/emoticons/Frown.png" class="emoticon" />')
	text = text.replace(/:O/gi   , '<img src="../resources/emoticons/giasp.png" class="emoticon" />')
	text = text.replace(/:D/gi   , '<img src="../resources/emoticons/girin.png" class="emoticon" />')
	text = text.replace(/D:/gi   , '<img src="../resources/emoticons/giasp.png" class="emoticon" />')
	text = text.replace(/ D:/gi  , '<img src="../resources/emoticons/giasp.png" class="emoticon" />')
	text = text.replace(/O:\)/gi , '<img src="../resources/emoticons/Halo.png" class="emoticon" />')
	text = text.replace(/\<3/gi  , '<img src="../resources/emoticons/Heart.png" class="emoticon" />')
	text = text.replace(/8\)/gi  , '<img src="../resources/emoticons/Wearing Sunglasses.png" class="emoticon" />')
	text = text.replace(/:\*/gi  , '<img src="../resources/emoticons/Kiss.png" class="emoticon" />')
	text = text.replace(/:\$/gi  , '<img src="../resources/emoticons/Money-mouth.png" class="emoticon" />')
	text = text.replace( /:P/gi  , '<img src="../resources/emoticons/Sticking Out Tongue.png" class="emoticon" />')
	return text
}
function format(text) {
	return hyperlink(smile(text))
}
