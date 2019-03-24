<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_profile']);
$player = check_user($secret_key, $db);

//Check for user ID
if (!$_GET['id']) {
	header("Location: members.php");
} else {
	$query = $db->execute("select `id`, `username`, `registered`, `level`, `kills`, `deaths`, `hp` from `players` where `username`=?", array($_GET['id']));
	if ($query->recordcount() == 0) {
		header("Location: members.php");
	} else {
		$profile = $query->fetchrow();
	}
}
require_once("templates/themes/" . $setting->theme . "/private_header.php");
?>
<fieldset>
<legend><?=$profile['username']?>'s <?=$lang['keyword_profile']?></legend>
<table width="90%">
<tr>
<td width="50%"><?=$lang['keyword_username']?>:</td>
<td width="50%"><?=$profile['username']?> (<a href="mail.php?act=compose&to=<?=$profile['username']?>"><?=$lang['keyword_mail']?></a>)</td>
</tr>
<tr>
<td><?=$lang['keyword_level']?>:</td>
<td><?=$profile['level']?></td>
</tr>
<tr><td></td></tr>
<tr>
<td><?=$lang['keyword_status']?>:</td>
<td><font color="<?=($profile['hp']==0)?"red\">Dead":"green\">Alive"?></font></td>
</tr>
<tr>
<td><?=$lang['keyword_kills']?>:</td>
<td><?=$profile['kills']?></td>
</tr>
<tr>
<td><?=$lang['keyword_deaths']?>:</td>
<td><?=$profile['deaths']?></td>
</tr>
<tr><td></td></tr>
<tr>
<td><?=$lang['keyword_registered']?>:</td>
<td><?=date("F j, Y, g:i a", $profile['registered'])?></td>
</tr>
<tr>
<td><?=$lang['keyword_age']?>:</td>
<?php
$diff = time() - $profile['registered'];
$age = intval(($diff / 3600) / 24);
?>
<td><?=$age?> <?=$lang['keyword_days']?></td>
</tr>
</table>
<br /><br />
<center>
<a href="battle.php?act=attack&username=<?=$profile['username']?>"><?=$lang['keyword_attack']?></a>
</center>
</fieldset>
<?php
require_once("templates/themes/" . $setting->theme . "/private_footer.php");
?>