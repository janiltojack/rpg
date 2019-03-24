<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_index']);

//Begin checking if user has tried to login
$error = 0; //Error count
$errormsg = "<font color=\"red\">"; //Error message to be displayed in case of error (modified below depending on error)
if ($_POST['login']) {
	$banquery = $db->execute("select `ban` from `players` where `username`=?", array($_POST['username']));
	$banned = $banquery->fetchrow();
	if ($_POST['username'] == "") {
		$errormsg .= $lang['error_enter_username'];
		$error = 1;
	}
	else if ($banned['ban'] >= time()) {
		$errormsg .= $lang['error_banned_username'];
		$error = 1;
	}
	else if ($_POST['password'] == "") {
		$errormsg .= $lang['error_enter_password'];
		$error = 1;
	}
	else if ($error == 0) {
		$query = $db->execute("select `id`, `username`, `gm_rank` from `players` where `username`=? and `password`=?", array($_POST['username'], sha1($_POST['password'])));
		if ($query->recordcount() == 0) {
			$errormsg .= $lang['error_login'];
			$error = 1;	
			//Clear user's session data
			session_unset();
			session_destroy();	
			//See if error logging is enabled
			if ($setting->index_log_error == "yes") {
				$logmsg = "IP " . $ip . " attempted to login with username " . $_POST['username'] . ".";
				errorlog($logmsg, $db);
			}
		} else {
			//See if IP logging is enabled for logging in
			if ($setting->index_log_ip == "yes") {
				$query2 = $db->execute("select `username` from `players` where `ip`=? or `last_ip`=?", array($ip, $ip));
				if ($query2->recordcount() > 0) {
					$multis = "The following users are playing with the same IP (" . $ip . "): <br />";
					while ($multi = $query2->fetchrow()) {
						$multis .= "<a href=\"users.php?id=" . $multi['username'] . "\">" . $multi['username'] . "</a> | ";
					}
					gmlog($multis, $db);
				}
			}							
			$player = $query->fetchrow();
			if ($setting->general_close_game == "yes" && $player['gm_rank'] <= 20) {
				$errormsg .= $lang['error_game_closed'];
				$error = 1;
			} else {
				$query = $db->execute("update `players` set `last_active`=?, `last_ip`=? where `id`=?", array(time(), $ip, $player['id']));
				$hash = sha1($player['id'] . $ip . $secret_key);
				$_SESSION['userid'] = $player['id'];
				$_SESSION['hash'] = $hash;
				header("Location: home.php");
			}
		}
	}
}
$errormsg .= "</font>";
require_once("templates/themes/" . $setting->theme . "/header.php");
?>
<table width="100%" border="0">
<tr>
<td width="60%">
Welcome to <?=$config_name?>!
Login now to play, or <a href="register.php">Register</a> to join the game!
<br /><br />
<i>Edit index.php to change this text and introduce your game.</i>
</td>
<td width="40%"><fieldset><legend>
<b><?=$lang['keyword_login']?></b></legend>
<?=($error==1)?$errormsg:""?>
<form method="POST" action="index.php">
<?=$lang['keyword_username']?> <input type="text" name="username" value="<?=$_POST['username']?>" /><br />
<?=$lang['keyword_password']?> <input type="password" name="password" /><br />
<input name="login" type="submit" value="<?=$lang['keyword_login']?>!" />
</form></fieldset>
</td>
</tr>
</table>
<?php
require_once("templates/themes/" . $setting->theme . "/footer.php");
?>