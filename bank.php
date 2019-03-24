<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_bank']);
$player = check_user($secret_key, $db);

//Calculate the bank account limit
if ($setting->bank_limit_type == 'variable') {
	$bank_limit = $setting->bank_limit * $player->level;
} else {
	$bank_limit = $setting->bank_limit;
}
//Process form submits
if (isset($_POST['deposit'])) {
	if ($_POST['deposit'] > $player->gold || $_POST['deposit'] <= 0) {
		$msg = "<font color=\"red\">" . $lang['error_deposit'] . "</font>\n";
	} else {
		if (($player->bank + $_POST['deposit']) > $bank_limit && $setting->bank_limit_type != 'unlimited') {
			$msg = "<font color=\"red\">" . $lang['error_bank_limit'] . "</font>\n";
		} else {
			$query = $db->execute("update `players` set `bank`=?, `gold`=? where `id`=?", array($player->bank + $_POST['deposit'], $player->gold - $_POST['deposit'], $player->id));
			$msg = "<font color=\"green\">" . $lang['msg_deposit'] . "</font>\n";
			$player = check_user($secret_key, $db); //Get new stats so new amount of gold is displayed on left menu
		}
	}
}
else if (isset($_POST['withdraw'])) {
	if ($_POST['withdraw'] > $player->bank || $_POST['withdraw'] <= 0) {
		$msg = "<font color=\"red\">" . $lang['error_withdraw'] . "</font>\n";
	} else {
		$query = $db->execute("update `players` set `bank`=?, `gold`=? where `id`=?", array($player->bank - $_POST['withdraw'], $player->gold + $_POST['withdraw'], $player->id));
		$msg = "<font color=\"green\">" . $lang['msg_withdraw'] . "</font>\n";
		$player = check_user($secret_key, $db); //Get new stats so new amount of gold is displayed on left menu
	}
}
else if (isset($_POST['interest'])) {
	$bank_interest_rate = intval($player->bank * ($setting->bank_interest_rate / 100));
	if ($player->interest == 0) {
		$query = $db->execute("update `players` set `interest`=1, `gold`=? where `id`=?", array($player->gold + $bank_interest_rate, $player->id));
		$msg = "<font color=\"green\">" . $lang['msg_interest_collected'] . "</font>\n";
		$player = check_user($secret_key, $db); //Get new stats so new amount of gold is displayed on left menu
	} else {
		$msg = "<font color=\"red\">" . $lang['error_interest'] . "</font>\n";
	}
}
//Main page
require_once("templates/themes/" . $setting->theme . "/private_header.php");
echo "<b>" . $lang['npc_bank'] . ":</b><br />\n<i>\n";
echo (isset($msg))?$msg:$lang['msg_bank_greeting'] . "\n";
echo "</i>";
?>
<br /><br />
<table width="100%">
<tr>
<td colspan="2">
<fieldset>
<legend><?=$lang['msg_bank_details']?></legend>
<b><?=$lang['keyword_username']?></b>: <?=$player->username?><br />
<?php
if ($setting->bank_limit_type == 'fixed' || $setting->bank_limit_type == 'variable') {
	echo $lang['msg_bank_limited'] . "<br />";
	echo sprintf($lang['msg_bank_limit'], $bank_limit);
	
} else {
	echo $lang['msg_bank_unlimited'] . "<br />";
}
?>
</legend>
</fieldset>
</td>
</tr>
<tr>
<td width="50%">
<fieldset>
<legend><?=$lang['keyword_deposit']?> <?=$lang['keyword_gold']?>:</legend>
<?=sprintf($lang['msg_gold'], $player->gold)?><br />
<form method="post" action="bank.php">
<input type="text" name="deposit" value="<?=$player->gold?>" />
<input type="submit" name="bank_action" value="<?=$lang['keyword_deposit']?>"/>
</form>
</fieldset>
</td>
<td rowspan="2" width="50%">
<fieldset class="empty">
<legend><?=$lang['keyword_collect']?> <?=$lang['keyword_interest']?></legend>
<?=sprintf($lang['msg_interest_rate'], $setting->bank_interest_rate);?>
<br /><br />
<form method="post" action="bank.php">
<?php
//Disable interest button once user has collected it
//Store action in 'interest' column in players table
//Reset that column with cron job
?>
<input type="submit" name="interest" value="<?=$lang['keyword_collect']?>!"<?=($player->interest == 1)?" disabled=\"disabled\"":""?>/>
</form>
<br />
<?=($player->interest == 1)?$lang['error_interest']:$lang['msg_collect_interest']?>
<br /><br />
<?=sprintf($lang['msg_daily_interest'], intval($player->bank * ($setting->bank_interest_rate / 100)));?>
</fieldset>
</td>
</tr>
<tr>
<td width="50%">
<fieldset>
<legend><?=$lang['keyword_withdraw']?> <?=$lang['keyword_gold']?>:</legend>
<?=sprintf($lang['msg_bank'], $player->bank)?><br />
<form method="post" action="bank.php">
<input type="text" name="withdraw" value="<?=$player->bank?>" />
<input type="submit" name="bank_action" value="Withdraw"/>
</form>
</fieldset>
</td>
</tr>
</table>
<?php
require_once("templates/themes/" . $setting->theme . "/private_footer.php");
?>