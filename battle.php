<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_battle']);
$player = check_user($secret_key, $db);

switch($_GET['act']) {
	case "attack":
		if (!$_GET['username']) { //No username entered 
			header("Location: battle.php");
			break;
		}
		//Otherwise, get player data:
		$query = $db->execute("select * from `players` where `username`=?", array($_GET['username']));
		if ($query->recordcount() == 0) { //Player doesn't exist
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo $lang['error_no_user'];
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		}
		$enemy1 = $query->fetchrow(); //Get player info
		foreach($enemy1 as $key=>$value) {
			$enemy->$key = $value;
		}
		//Otherwise, check if player has any health
		if ($enemy->hp <= 0) {
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo $lang['error_dead_user'];
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		}
		//Player cannot attack anymore
		if ($player->energy <= 0) {
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo $lang['error_no_energy'];
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		}
		//Player is dead
		if ($player->hp <= 0) {
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo $lang['error_dead'];
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		}
		//Can't attack yourself...
		if ($enemy->username == $player->username) {
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo $lang['error_attack_self'];
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		}
		//Whether this limitation is enabled can be set in GM panel
		//Enemy is too low level
		if ($enemy->level < ($player->level - $setting->battle_min_level)) {
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo $lang['error_low_level'];
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		}	
		//Get enemy's bonuses from equipment
		$query = $db->query("select blueprint_items.effectiveness, blueprint_items.name from `items`, `blueprint_items` where blueprint_items.id=items.item_id and items.player_id=? and blueprint_items.type='weapon' and items.status='equipped'", array($enemy->id));
		$enemy->atkbonus = ($query->recordcount() == 1)?$query->fetchrow():0;
		$query = $db->query("select blueprint_items.effectiveness, blueprint_items.name from `items`, `blueprint_items` where blueprint_items.id=items.item_id and items.player_id=? and blueprint_items.type='armour' and items.status='equipped'", array($enemy->id));
		$enemy->defbonus = ($query->recordcount() == 1)?$query->fetchrow():0;
		//Get player's bonuses from equipment
		$query = $db->query("select blueprint_items.effectiveness, blueprint_items.name from `items`, `blueprint_items` where blueprint_items.id=items.item_id and items.player_id=? and blueprint_items.type='weapon' and items.status='equipped'", array($player->id));
		$player->atkbonus = ($query->recordcount() == 1)?$query->fetchrow():0;
		$query = $db->query("select blueprint_items.effectiveness, blueprint_items.name from `items`, `blueprint_items` where blueprint_items.id=items.item_id and items.player_id=? and blueprint_items.type='armour' and items.status='equipped'", array($player->id));
		$player->defbonus = ($query->recordcount() == 1)?$query->fetchrow():0;
		//Calculate some variables that will be used
		$enemy->strdiff = (($enemy->strength - $player->strength) > 0)?($enemy->strength - $player->strength):0;
		$enemy->vitdiff = (($enemy->vitality - $player->vitality) > 0)?($enemy->vitality - $player->vitality):0;
		$enemy->agidiff = (($enemy->agility - $player->agility) > 0)?($enemy->agility - $player->agility):0;
		$player->strdiff = (($player->strength - $enemy->strength) > 0)?($player->strength - $enemy->strength):0;
		$player->vitdiff = (($player->vitality - $enemy->vitality) > 0)?($player->vitality - $enemy->vitality):0;
		$player->agidiff = (($player->agility - $enemy->agility) > 0)?($player->agility - $enemy->agility):0;
		$totalstr = $enemy->strength + $player->strength;
		$totalvit = $enemy->vitality + $player->vitality;
		$totalagi = $enemy->agility + $player->agility;
		//Calculate the damage to be dealt by each player (dependent on strength and vitality)
		$enemy->maxdmg = (($enemy->strength * 2) + $enemy->atkbonus['effectiveness']) - ($player->defbonus['effectiveness']);
		$enemy->maxdmg = $enemy->maxdmg - intval($enemy->maxdmg * ($player->vitdiff / $totalvit));
		$enemy->maxdmg = ($enemy->maxdmg <= 2)?2:$enemy->maxdmg; //Set 2 as the minimum damage
		$enemy->mindmg = (($enemy->maxdmg - 4) < 1)?1:($enemy->maxdmg - 4); //Set a minimum damage range of maxdmg-4
		$player->maxdmg = (($player->strength * 2) + $player->atkbonus['effectiveness']) - ($enemy->defbonus['effectiveness']);
		$player->maxdmg = $player->maxdmg - intval($player->maxdmg * ($enemy->vitdiff / $totalvit));
		$player->maxdmg = ($player->maxdmg <= 2)?2:$player->maxdmg; //Set 2 as the minimum damage
		$player->mindmg = (($player->maxdmg - 4) < 1)?1:($player->maxdmg - 4); //Set a minimum damage range of maxdmg-4
		//Calculate battle 'combos' - how many times in a row a player can attack (dependent on agility)
		$enemy->combo = ceil($enemy->agility / $player->agility);
		$enemy->combo = ($enemy->combo > 3)?3:$enemy->combo;
		$player->combo = ceil($player->agility / $enemy->agility);
		$player->combo = ($player->combo > 3)?3:$player->combo;
		//Calculate the chance to miss opposing player
		$enemy->miss = intval(($player->agidiff / $totalagi) * 100);
		$enemy->miss = ($enemy->miss > 20)?20:$enemy->miss; //Maximum miss chance of 20% (possible to change in admin panel?)
		$enemy->miss = ($enemy->miss <= 5)?5:$enemy->miss; //Minimum miss chance of 5%
		$player->miss = intval(($enemy->agidiff / $totalagi) * 100);
		$player->miss = ($player->miss > 20)?20:$player->miss; //Maximum miss chance of 20%
		$player->miss = ($player->miss <= 5)?5:$player->miss; //Minimum miss chance of 5%
		$battlerounds = $setting->battle_round_limit; //Maximum number of rounds/turns in the battle. Changed in admin panel?
		$output = ""; //Output message
		$output .= sprintf($lang['msg_battle_start'], $player->username, $enemy->username) . "<br /><br />\n";
		//While somebody is still alive, battle!
		while ($enemy->hp > 0 && $player->hp > 0 && $battlerounds > 0) {
			$attacking = ($player->agility >= $enemy->agility)?$player:$enemy;
			$defending = ($player->agility >= $enemy->agility)?$enemy:$player;	
			for($i = 0;$i < $attacking->combo;$i++)	{
				//Chance to miss?
				$misschance = intval(rand(0, 100));
				if ($misschance <= $attacking->miss)	{
					$output .= sprintf($lang['msg_battle_miss'], $attacking->username, $defending->username);
					$output .= "<br />\n";
				} else {
					$damage = rand($attacking->mindmg, $attacking->maxdmg); //Calculate random damage				
					$defending->hp -= $damage;
					$output .= ($player->username == $defending->username)?"<font color=\"red\">":"<font color=\"green\">";
					$output .= sprintf($lang['msg_battle_attack'], $attacking->username, $defending->username, $damage);
					$output .= " (";
					$output .= ($defending->hp > 0)?sprintf($lang['msg_battle_status'], $defending->hp):$lang['keyword_dead'];
					$output .= ")<br />";
					$output .= "</font>\n";
					//Check if anybody is dead
					if ($defending->hp <= 0) {
						$player = ($player->agility >= $enemy->agility)?$attacking:$defending;
						$enemy = ($player->agility >= $enemy->agility)?$defending:$attacking;
						break 2; //Break out of the for and while loop, but not the switch structure
					}
				}
				$battlerounds--;
				if ($battlerounds <= 0) {
					break 2; //Break out of for and while loop, battle is over!
				}
			}	
			for($i = 0;$i < $defending->combo;$i++)	{
				//Chance to miss?
				$misschance = intval(rand(0, 100));
				if ($misschance <= $defending->miss)	{
					$output .= sprintf($lang['msg_battle_miss'], $defending->username, $attacking->username);
					$output .= "<br />\n";
				} else {
					$damage = rand($defending->mindmg, $defending->maxdmg); //Calculate random damage
					$attacking->hp -= $damage;
					$output .= ($player->username == $defending->username)?"<font color=\"green\">":"<font color=\"red\">";
					$output .= sprintf($lang['msg_battle_attack'], $defending->username, $attacking->username, $damage);
					$output .= " (";
					$output .= ($attacking->hp > 0)?sprintf($lang['msg_battle_status'], $attacking->hp):$lang['keyword_dead'];
					$output .= ")<br />";
					$output .= "</font>\n";

					//Check if anybody is dead
					if ($attacking->hp <= 0)	{
						$player = ($player->agility >= $enemy->agility)?$attacking:$defending;
						$enemy = ($player->agility >= $enemy->agility)?$defending:$attacking;
						break 2; //Break out of the for and while loop, but not the switch structure
					}
				}
				$battlerounds--;
				if ($battlerounds <= 0)	{
					break 2; //Break out of for and while loop, battle is over!
				}
			}	
			$player = ($player->agility >= $enemy->agility)?$attacking:$defending;
			$enemy = ($player->agility >= $enemy->agility)?$defending:$attacking;
		}
		$battlelog = $output;
		if ($player->hp <= 0)	{
			//Calculate losses
			$exploss1 = $player->level * 6;
			$exploss2 = (($player->level - $enemy->level) > 0)?($enemy->level - $player->level) * 4:0;
			$exploss = $exploss1 + $exploss2;
			$goldloss = intval(0.2 * $player->gold);
			$goldloss = intval(rand(1, $goldloss));
			
			$output .= "<br />" . sprintf($lang['msg_battle_defeated'], $enemy->username) . "<br />\n";
			$battlelog .= "<br />" . sprintf($lang['msg_battle_won'], $player->username) . "<br />\n";
			$output .= "<br />" . sprintf($lang['msg_battle_losses'], $exploss, $goldloss) . "\n";
			$battlelog .= "<br />" . sprintf($lang['msg_battle_winnings'], $exploss, $goldloss) . "\n";
			$exploss3 = (($player->exp - $exploss) <= 0)?$player->exp:$exploss;
			$goldloss2 = (($player->gold - $goldloss) <= 0)?$player->gold:$goldloss;
			//Update player (the loser)
			$query = $db->execute("update `players` set `energy`=?, `exp`=?, `gold`=?, `deaths`=?, `hp`=0 where `id`=?", array($player->energy - 1, $player->exp - $exploss3, $player->gold - $goldloss2, $player->deaths + 1, $player->id));
			
			//Update enemy (the winner)
			if ($exploss + $enemy->exp < $enemy->maxexp)	{
				$query = $db->execute("update `players` set `exp`=?, `gold`=?, `kills`=?, `hp`=? where `id`=?", array($enemy->exp + $exploss, $enemy->gold + $goldloss, $enemy->kills + 1, $enemy->hp, $enemy->id));
				//Add log message for winner
				$logmsg = sprintf($lang['msg_log_won'], $player->username, $exploss, $goldloss);
				addlog($enemy->id, $logmsg, $battlelog, $db);
			} else { //Defender has gained a level! =)	
				$query = $db->execute("update `players` set `stat_points`=?, `level`=?, `maxexp`=?, `exp`=?, `gold`=?, `kills`=?, `hp`=?, `maxhp`=? where `id`=?", array($enemy->stat_points + 3, $enemy->level + 1, ($enemy->level+1) * 70 - 20, ($enemy->exp + $exploss) - $enemy->maxexp, $enemy->gold + $goldloss, $enemy->kills + 1, $enemy->maxhp + 30, $enemy->maxhp + 30, $enemy->id));
				//Add log message for winner
				$logmsg = sprintf($lang['msg_log_level'], $player->username, $goldloss);
				addlog($enemy->id, $logmsg, $battlelog, $db);
			}
		}
		else if ($enemy->hp <= 0) {
			//Calculate losses
			$expwin1 = $enemy->level * 6;
			$expwin2 = (($player->level - $enemy->level) > 0)?$expwin1 - (($player->level - $enemy->level) * 3):$expwin1 + (($player->level - $enemy->level) * 3);
			$expwin2 = ($expwin2 <= 0)?1:$expwin2;
			$expwin3 = round(0.6 * $expwin2);
			$expwin = ceil(rand($expwin3, $expwin2));
			$goldwin = ceil(0.2 * $enemy->gold);
			$goldwin = intval(rand(1, $goldwin));
			$output .= "<br />" . sprintf($lang['msg_battle_won'], $enemy->username) . "<br />\n";
			$battlelog .= "<br />" . sprintf($lang['msg_battle_defeated'], $player->username) . "<br />\n";
			$output .= "<br />" . sprintf($lang['msg_battle_winnings'], $expwin, $goldwin) . "\n";				
			if ($expwin + $player->exp >= $player->maxexp) { //Player gained a level!
				//Update player, gained a level
				$output .= "<br /><b>" . $lang['msg_levelup'] . "</b>";
				$newexp = $expwin + $player->exp - $player->maxexp;
				$query = $db->execute("update `players` set `stat_points`=?, `level`=?, `maxexp`=?, `maxhp`=?, `exp`=?, `gold`=?, `kills`=?, `hp`=?, `energy`=? where `id`=?", array($player->stat_points + 3, $player->level + 1, ($player->level+1) * 70 - 20, $player->maxhp + 30, $newexp, $player->gold + $goldwin, $player->kills + 1, $player->maxhp + 30, $player->energy - 1, $player->id));
			} else {
				//Update player
				$query = $db->execute("update `players` set `exp`=?, `gold`=?, `kills`=?, `hp`=?, `energy`=? where `id`=?", array($player->exp + $expwin, $player->gold + $goldwin, $player->kills + 1, $player->hp, $player->energy - 1, $player->id));
			}
			//Add log message
			$logmsg = sprintf($lang['msg_log_lost'], $player->username);
			addlog($enemy->id, $logmsg, $battlelog, $db);
			//Update enemy (who was defeated)
			$query = $db->execute("update `players` set `hp`=0, `deaths`=? where `id`=?", array($enemy->deaths + 1, $enemy->id));
		} else {
			$output .= "<br />" . $lang['msg_battle_draw'] . "\n";
			$battlelog .= "<br />" . $lang['msg_battle_draw'] . "\n";
			$query = $db->execute("update `players` set `hp`=?, `energy`=? where `id`=?", array($player->hp, $player->energy - 1, $player->id));
			$query = $db->execute("update `players` set `hp`=? where `id`=?", array($enemy->hp, $enemy->id));	
			$logmsg = sprintf($lang['msg_log_draw'], $player->username);
			addlog($enemy->id, $logmsg, $battlelog, $db);
		}
		$player = check_user($secret_key, $db); //Get new stats
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo $output;
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		break;
	case "search" :
		//Check in case somebody entered 0
		$_GET['fromlevel'] = ($_GET['fromlevel'] == 0)?"":$_GET['fromlevel'];
		$_GET['tolevel'] = ($_GET['tolevel'] == 0)?"":$_GET['tolevel'];
		//Construct query
		$query = "select `id`, `username`, `hp`, `maxhp`, `level` from `players` where `id`!= ? and ";
		$query .= ($_GET['username'] != "")?"`username` LIKE  ? and ":"";
		$query .= ($_GET['fromlevel'] != "")?"`level` >= ? and ":"";
		$query .= ($_GET['tolevel'] != "")?"`level` <= ? and ":"";
		$query .= ($_GET['alive'] == "1")?"`hp` > 0 ":"`hp` = 0 ";
		$query .= "limit 20";
		//Construct values array for adoDB
		$values = array();
		array_push($values, $player->id); //Make sure battle search doesn't show self
		if ($_GET['username'] != "") {
			array_push($values, "%".trim($_GET['username'])."%"); //Add username value for search
		}
		//Add level range for search
		if ($_GET['fromlevel']) {
			array_push($values, intval($_GET['fromlevel']));
		}
		if ($_GET['tolevel']) {
			array_push($values, intval($_GET['tolevel']));
		}
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		//Display search form again
		echo "<fieldset>\n";
		echo "<legend><b>" . $lang['msg_player_search'] . "</b></legend>\n";
		echo "<form method=\"get\" action=\"battle.php\">\n<input type=\"hidden\" name=\"act\" value=\"search\" />\n";
		echo "<table width=\"100%\">\n";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_username'] . ":</td>\n<td width=\"60%\"><input type=\"text\" name=\"username\" value=\"" . stripslashes($_GET['username']) . "\" /></td>\n</tr>\n";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_level'] . "</td>\n<td width=\"60%\"><input type=\"text\" name=\"fromlevel\" size=\"4\" value=\"" . stripslashes($_GET['fromlevel']) . "\" /> to <input type=\"text\" name=\"tolevel\" size=\"4\" value=\"" . stripslashes($_GET['tolevel']) . "\" /></td>\n</tr>\n";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_status'] . ":</td>\n<td width=\"60%\"><select name=\"alive\" size=\"2\">\n<option value=\"1\"";
		echo ($_GET['alive'] == 1)?" selected=\"selected\"":"";
		echo ">" . $lang['keyword_alive'] . "</option>\n<option value=\"0\"";
		echo ($_GET['alive'] == 0)?" selected=\"selected\"":"";
		echo ">" . $lang['keyword_dead'] . "</option>\n</select></td>\n</tr>\n";
		echo "<tr><td></td><td><br /><input type=\"submit\" value=\"" . $lang['keyword_search'] . "!\" /></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n</fieldset>\n";
		echo "<br /><br />";
		echo "<table id=\"forum-list\" width=\"100%\">\n";
		echo "<tr><th width=\"50%\" align=\"left\">" . $lang['keyword_username'] . "</th><th width=\"20%\" align=\"left\">" . $lang['keyword_level'] . "</th><th width=\"30%\" align=\"left\">" . $lang['keyword_battle'] . "</a></th></tr>\n";
		$query = $db->execute($query, $values); //Search!
		if ($query->recordcount() > 0) { //Check if any players were found
			$bool = 1;
			while ($result = $query->fetchrow()) {
				echo "<tr class=\"row" . $bool . "\">\n";
				echo "<td width=\"50%\"><a href=\"profile.php?username=" . $result['username'] . "\">" . $result['username'] . "</a></td>\n";
				echo "<td width=\"20%\">" . $result['level'] . "</td>\n";
				echo "<td width=\"30%\"><a href=\"battle.php?act=attack&username=" . $result['username'] . "\">" . $lang['keyword_attack'] . "</a></td>\n";
				echo "</tr>\n";
				$bool = ($bool==1)?2:1;
			}
		} else {
			echo "<tr>\n";
			echo "<td colspan=\"3\">" . $lang['error_no_players'] . "</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		break;
	default:
		require_once("templates/themes/" . $setting->theme . "/private_header.php");	
		//The default battle page, giving choice of whether to search for players or to target one
		echo "<fieldset>\n";
		echo "<legend><b>" . $lang['msg_player_search'] . "</b></legend>\n";
		echo "<form method=\"get\" action=\"battle.php\">\n<input type=\"hidden\" name=\"act\" value=\"search\" />\n";
		echo "<table width=\"100%\">\n";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_username'] . ":</td>\n<td width=\"60%\"><input type=\"text\" name=\"username\" /></td>\n</tr>\n";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_level'] . "</td>\n<td width=\"60%\"><input type=\"text\" name=\"fromlevel\" size=\"4\" /> to <input type=\"text\" name=\"tolevel\" size=\"4\" /></td>\n</tr>\n";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_status'] . ":</td>\n<td width=\"60%\"><select name=\"alive\" size=\"2\">\n<option value=\"1\" selected=\"selected\">" . $lang['keyword_alive'] . "</option>\n<option value=\"0\">" . $lang['keyword_dead'] . "</option>\n</select></td>\n</tr>\n";
		echo "<tr><td></td><td><br /><input type=\"submit\" value=\"" . $lang['keyword_search'] . "!\" /></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n</fieldset>\n";
		echo "<br /><br />\n";
		echo "<fieldset>\n";
		echo "<legend><b>" . $lang['msg_attack_player'] . "</b></legend>\n";
		echo "<form method=\"get\" action=\"battle.php?act=attack\">\n<input type=\"hidden\" name=\"act\" value=\"attack\" />\n";
		echo $lang['keyword_username'] . ":&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"username\" /><br />\n";
		echo "<input type=\"submit\" value=\"" . $lang['keyword_battle'] . "!\" />\n";
		echo "</form>\n";
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		break;
}
?>