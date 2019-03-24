<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_register']);

if ($setting->register_status == "closed") {
	require_once("templates/themes/" . $setting->theme . "/header.php");
	echo "Sorry, registration is closed!<br />\n";
	require_once("templates/themes/" . $setting->theme . "/footer.php");
	exit;
}
$msg1 = "<font color=\"red\">"; //Username error?
$msg2 = "<font color=\"red\">"; //Password error?
$msg3 = "<font color=\"red\">"; //Verify Password error?
$msg4 = "<font color=\"red\">"; //Email error?
$msg5 = "<font color=\"red\">"; //Verify Email error?
$msg6 = "<font size=\"3\" color=\"red\">"; //IP ban error?
$error = 0;
if ($_POST['register']) {	
	$query = $db->execute("select `id` from `players` where `username`=?", array($_POST['username']));
	//Check username
	if (!$_POST['username']) { //If username isn't filled in...
		$msg1 .= $lang['error_enter_username'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	}
	else if (strlen($_POST['username']) < 3)
	{ //If username is too short...
		$msg1 .= $lang['error_short_username'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	}
	else if (!preg_match("/^[-_a-zA-Z0-9]+$/", $_POST['username']))
	{ //If username contains illegal characters...
		$msg1 .= $lang['error_char_username'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	}
	else if ($query->recordcount() > 0)
	{
		$msg1 .= $lang['error_username_taken'] . " " . $lang['msg_one_account'] . "<br />\n";
		$error = 1; //Set error check
	}
	//Check password
	if (!$_POST['password']) { //If password isn't filled in...
		$msg2 .= $lang['error_enter_password'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	}
	else if ($_POST['password'] != $_POST['password2'])
	{
		$msg3 .= $lang['error_verify_password'] . "<br />\n";
		$error = 1;
	}
	else if (strlen($_POST['password']) < 3)
	{ //If password is too short...
		$msg2 .= $lang['error_short_password'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	}
	else if (!preg_match("/^[-_a-zA-Z0-9]+$/", $_POST['password']))
	{ //If password contains illegal characters...
		$msg2 .= $lang['error_char_password'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	}
	//Check email
	if (!$_POST['email']) { //If email address isn't filled in...
		$msg4 .= $lang['error_enter_email'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	}
	else if ($_POST['email'] != $_POST['email2'])
	{
		$msg5 .= $lang['error_verify_email'] . "<br />\n";
		$error = 1;
	}
	else if (strlen($_POST['email']) < 3)
	{ //If email is too short...
		$msg4 .= $lang['error_short_email'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	}
	else if (!preg_match("/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i", $_POST['email']))
	{
		$msg4 .= $lang['error_email_format'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	} else {
		//Check if email has already been used
		$query = $db->execute("select `id` from `players` where `email`=?", array($_POST['email']));
		if ($query->recordcount() > 0)
		{
			$msg4 .= $lang['error_email_taken'] . " " . $lang['msg_one_account'] . "<br />\n";
			$error = 1; //Set error check
		}
	}
	//Check if ip has been banned
	$query = $db->execute("select `id` from `players` where `ip`=? and `ban`>=?", array($ip, time()));
	if($query->recordcount() > 0) { //If ip is banned...
		$msg6 .= $lang['error_banned_ip'] . "<br />\n"; //Add to error message
		$error = 1; //Set error check
	}		
	if ($error == 0) {
		$insert['username'] = $_POST['username'];
		$insert['password'] = sha1($_POST['password']);
		$insert['email'] = $_POST['email'];
		$insert['registered'] = time();
		$insert['last_active'] = time();
		$insert['ip'] = $ip;
		$query = $db->autoexecute('players', $insert, 'INSERT');				
		if (!$query) {
			$could_not_register = $lang['error_register'] . "<br /><br />";
			
			$logmsg = "IP " . $ip . " attempted to register, but an error occurred.";
			errorlog($logmsg, $db);
		} else {
			$insertid = $db->Insert_ID();	
			//If IP logging is enabled for registration
			if ($config_register_multis == 1) {
				$query2 = $db->execute("select `username` from `players` where `ip`=? or `last_ip`=?", array($ip, $ip));
				if ($query2->recordcount() > 0) {
					$multis = "The following users have registered with the same IP (" . $ip . "): <br />";
					while ($multi = $query2->fetchrow()) {
						$multis .= "<a href=\"users.php?id=" . $multi['username'] . "\">" . $multi['username'] . "</a> | ";
					}
				}
				gmlog($multis, $db);
			}
			require_once("templates/themes/" . $setting->theme . "/header.php");
			echo $lang['msg_registered'];
			require_once("templates/themes/" . $setting->theme . "/footer.php");
			exit;
		}
	}
}
$msg1 .= "</font>"; //Username error?
$msg2 .= "</font>"; //Password error?
$msg3 .= "</font>"; //Verify Password error?
$msg4 .= "</font>"; //Email error?
$msg5 .= "</font>"; //Verify Email error?
require_once("templates/themes/" . $setting->theme . "/header.php");
?>
<?=$could_not_register?>
<?=$msg6;?>
<br>
<form method="POST" action="register.php">
<table width="100%">
<tr><td width="40%"><b><?=$lang['keyword_username']?></b></td><td><input type="text" name="username" value="<?=$_POST['username'];?>" /></td></tr>
<tr><td colspan="2"><?=$lang['msg_enter_username']?> <?=$lang['msg_characters']?><br /><?=$msg1;?><br /></td></tr>
<tr><td width="40%"><b><?=$lang['keyword_password']?></b></td><td><input type="password" name="password" value="<?=$_POST['password'];?>" /></td></tr>
<tr><td colspan="2"><?=$lang['msg_enter_password']?> <?=$lang['msg_characters']?><br /><?=$msg2;?><br /></td></tr>
<tr><td width="40%"><b><?=$lang['keyword_verify_pass']?></b></td><td><input type="password" name="password2" value="<?=$_POST['password2'];?>" /></td></tr>
<tr><td colspan="2"><?=$lang['msg_verify_password']?><br /><?=$msg3;?><br /></td></tr>
<tr><td width="40%"><b><?=$lang['keyword_email']?></b></td><td><input type="text" name="email" value="<?=$_POST['email'];?>" /></td></tr>
<tr><td colspan="2"><?=$lang['msg_enter_email']?> <?=$lang['msg_characters']?><br /><?=$msg4;?><br /></td></tr>
<tr><td width="40%"><b><?=$lang['keyword_verify_email']?></b></td><td><input type="text" name="email2" value="<?=$_POST['email2'];?>" /></td></tr>
<tr><td colspan="2"><?=$lang['msg_verify_email']?><br /><?=$msg5;?><br /></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="register" value="<?=$lang['keyword_register']?>!"></td></tr>
</table>
</form>
<?php
require_once("templates/themes/" . $setting->theme . "/footer.php");
?>