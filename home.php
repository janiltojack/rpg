<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_home']);
$player = check_user($secret_key, $db);
// $db->debug = true; //Debug

require_once("templates/themes/" . $setting->theme . "/private_header.php");
?>
<table>
<tr>
<td width="2%"></td>
<td width="45%" align="left" valign="top">
<b><?=$lang['keyword_username']?>:</b> <?=$player->username?><br />
<b><?=$lang['keyword_email']?>:</b> <?=$player->email?><br />
<b><?=$lang['keyword_registered']?>:</b> <?=date("F j, Y, g:i a", $player->registered)?><br />
<?php
$diff = time() - $player->registered;
$age = intval(($diff / 3600) / 24);
?>
<b><?=$lang['keyword_age']?>:</b> <?=$age?> <?=$lang['keyword_days']?><br />
<b><?=$lang['keyword_kills']?>/<?=$lang['keyword_deaths']?>:</b> <?=$player->kills?>/<?=$player->deaths?><br />
<br />
</td>
<td width="6%"></td>
<td width="45%" align="left" valign="top">
<b><?=$lang['keyword_level']?>:</b> <?=$player->level?><br />
<?php
$percent = intval(($player->exp / $player->maxexp) * 100);
?>
<b><?=$lang['keyword_exp']?>:</b> <?=$player->exp?>/<?=$player->maxexp?> (<?=$percent?>%)<br />
<b><?=$lang['keyword_hp']?>:</b> <?=$player->hp?>/<?=$player->maxhp?><br />
<b><?=$lang['keyword_energy']?>:</b> <?=$player->energy?>/<?=$player->maxenergy?><br />
<b><?=$lang['keyword_gold']?>:</b><?=$player->gold?><br />
<br />
<b><?=$lang['keyword_strength']?>:</b> <?=$player->strength?><br />
<b><?=$lang['keyword_vitality']?>:</b> <?=$player->vitality?><br />
<b><?=$lang['keyword_agility']?>:</b><?=$player->agility?><br />
</td>
<td width="2%"></td>
</tr>

<br />
<?php
echo '<tr><td align="center" colspan="5"><br />';
if ($player->stat_points > 0){
	echo sprintf($lang['msg_spend_statpoints'], $player->stat_points);
} else {
	echo $lang['error_no_statpoints'];
}
echo '</center>';
echo '</td>';
echo '</tr>';
// Awards section
//echo '<tr>';
//echo '<td align="center" colspan="5">';

//$query = $db->execute('select * from trophies');
//while ($test = $query->fetchrow()) {
//$stat = $test['stat'];
//$oper = $test['operator'];
//$crit = $test['criteria'];
//$out = $test['output'];
//$msg= $stat >= $crit;


	//if ($player->$stat >= $crit) {
		//echo "<img src=\"".$out."\" width=\"100px\" height=\"100px\">";
		//echo " ";
	//}

//}
//echo '</td></tr>';
// End of awards
echo '</table>';
require_once("templates/themes/" . $setting->theme . "/private_footer.php");
?>