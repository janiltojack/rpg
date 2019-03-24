<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
include("lib.php");
define("PAGENAME", $lang['page_log']);
$player = check_user($secret_key, $db);

if ($_GET['act'] == "clear") {
	//Clear all log messages for current user
	$query = $db->execute("delete from `user_log` where `player_id`=?", array($player->id));
}
else if ($_GET['act'] == "read") //Reading further details (eg., complete battle report)
{
	$query = $db->execute("select `id`, `full_msg`, `time` from `user_log` where `id`=? and `player_id`=?", array($_GET['id'], $player->id));
	if ($query->recordcount() == 0) {
		header("Location: log.php"); //Message doesn't exist or message doesn't belong to user
		exit;
	}
	$logmsg = $query->fetchrow();
	
	include("templates/themes/" . $setting->theme . "/private_header.php");
	echo sprintf($lang['msg_this_event'], date("F j, Y, g:i a", $logmsg['time']));
	echo "\n<br /><br />\n";
	echo $logmsg['full_msg'];
	echo "\n<br /><br />\n";
	echo "<a href=\"log.php\">" . $lang['keyword_back'] . "</a>";
	include("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
//Get all log messages ordered by status
$query = $db->execute("select `id`, `msg`, `full_msg`, `status`, `time` from `user_log` where `player_id`=? order by `id` desc", array($player->id));
//Update the status of the messages because now they have been read
$query2 = $db->execute("update `user_log` set `status`='read' where `player_id`=? and `status`='unread'", array($player->id));

include("templates/themes/" . $setting->theme . "/private_header.php");

if ($query->recordcount() > 0) {
	echo "<a href=\"log.php?act=clear\">" . $lang['msg_clear_log'] . "</a>";
	while ($log = $query->fetchrow()){
		echo "<fieldset>\n";
		echo "<legend>";
		echo ($log['status']=="unread")?"<b>" . $lang['keyword_unread'] . "</b>":$lang['keyword_read'];
		echo " - " . date("F j, Y, g:i a", $log['time']) . "\n";
		echo "</legend>\n";
		echo $log['msg'] . "\n";
		if ($log['full_msg'] != ""){
			echo "<br />\n<a href=\"log.php?act=read&id=" . $log['id'] . "\">" . $lang['keyword_more'] . "</a>\n";
		}
		echo "</fieldset>\n<br />\n";
	}
} else {
	echo $lang['error_no_msg'];
}
include("templates/themes/" . $setting->theme . "/private_footer.php");
?>