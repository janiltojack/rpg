<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_stats']);
$player = check_user($secret_key, $db);

if ($player->stat_points > 0) {
	switch($_GET['act']) {
		case '0':
			$query = $db->execute("update `players` set `stat_points`=?, `strength`=? where `id`=?", array($player->stat_points - 1, $player->strength + 1, $player->id));
			if ($query) {
				$player = check_user($secret_key, $db); //Get new stats
				$msg = "<b>" . sprintf($lang['msg_trained_str'], $player->strength) . "</b><br /><br />";
			} else {
				//Error, insert error into log
			}
			break;
		case '1':
			//Add to vitality, and update health
			//Health increase should be able to be changed from admin panel
			$query = $db->execute("update `players` set `stat_points`=?, `vitality`=?, `maxhp`=? where `id`=?", array($player->stat_points - 1, $player->vitality + 1, $player->maxhp + 20, $player->id));
			if ($query) {
				$player = check_user($secret_key, $db); //Get new stats
				$msg = "<b>" . sprintf($lang['msg_trained_vit'], $player->vitality) . "</b><br /><br />";
			} else {
				//Error, insert error into log
			}
			break;
		case '2':
			$query = $db->execute("update `players` set `stat_points`=?, `agility`=? where `id`=?", array($player->stat_points - 1, $player->agility + 1, $player->id));
			if ($query) {
				$player = check_user($secret_key, $db); //Get new stats
				$msg = "<b>" . sprintf($lang['msg_trained_agi'], $player->agility) . "</b><br /><br />";
			} else {
				//Error, insert error into log
			}
			break;
	}
}
require_once("templates/themes/" . $setting->theme . "/private_header.php");
echo $msg;
if ($player->stat_points == 0) {
?>
<b><?=$lang['npc_stats']?>:</b><br />
<i><?=$lang['error_no_statpoints2']?></i>
<?php
} else {
?>
<b><?=$lang['npc_stats']?>:</b><br />
<i><?=sprintf($lang['msg_stats_greeting'], $player->stat_points)?></i>
<br /><br />
<a href="stat_points.php?act=0"><?=$lang['keyword_strength']?></a><br />
<a href="stat_points.php?act=1"><?=$lang['keyword_vitality']?></a><br />
<a href="stat_points.php?act=2"><?=$lang['keyword_agility']?></a><br />
<?php
}
require_once("templates/themes/" . $setting->theme . "/private_footer.php");
?>