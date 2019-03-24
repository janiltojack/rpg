<?php
/*************************************/
/*       Modified for X-Panel        */
/*************************************/


include("lib.php");


define("PAGENAME", "Fight Some Monsters");

$player = check_user($secret_key, $db);





switch($_GET['act'])

{

	case "attack":

		if (!$_GET['username']) //No username entered

		{

			header("Location: monsters.php");

			break;

		}

		

		//Otherwise, get player data:

		$query = $db->execute("select * from `monsters` where `username`=?", array($_GET['username']));

		if ($query->recordcount() == 0) //Player doesn't exist

		{

			require_once("templates/themes/" . $setting->theme . "/private_header.php");

			echo "This player doesn't exist!";

			require_once("templates/themes/" . $setting->theme . "/private_footer.php");

			break;

		}

		

		$enemy1 = $query->fetchrow(); //Get monster info

		foreach($enemy1 as $key=>$value)

		{

			$enemy->$key = $value;

		}

		

		//Player cannot attack anymore

		if ($player->energy == 0)

		{

			require_once("templates/themes/" . $setting->theme . "/private_header.php");

			echo "You have no energy left! You must rest a while.";

			require_once("templates/themes/" . $setting->theme . "/private_footer.php");

			break;

		}

		

		//Player is dead

		if ($player->hp == 0)

		{

			require_once("templates/themes/" . $setting->theme . "/private_header.php");

			echo "You are dead! Please visit the hospital or wait until you are revived!";

			require_once("templates/themes/" . $setting->theme . "/private_footer.php");

			break;

		}

		

		

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

		

		

		$battlerounds = 30; //Maximum number of rounds/turns in the battle. Changed in admin panel?

		

		$output = ""; //Output message

		

		

		//While somebody is still alive, battle!

		while ($enemy->hp > 0 && $player->hp > 0 && $battlerounds > 0)

		{

			$attacking = ($player->agility >= $enemy->agility)?$player:$enemy;

			$defending = ($player->agility >= $enemy->agility)?$enemy:$player;

			

			for($i = 0;$i < $attacking->combo;$i++)

			{

				//Chance to miss?

				$misschance = intval(rand(0, 100));

				if ($misschance <= $attacking->miss)

				{

					$output .= $attacking->username . " tried to attack the " . $defending->username . " but missed!<br />";

				}

				else

				{

					$damage = rand($attacking->mindmg, $attacking->maxdmg); //Calculate random damage				

					$defending->hp -= $damage;

					$output .= ($player->username == $defending->username)?"<font color=\"red\">":"<font color=\"green\">";

					$output .= $attacking->username . " attacks " . $defending->username . " for <b>" . $damage . "</b> damage! (";

					$output .= ($defending->hp > 0)?$defending->hp . " HP left":"Dead";

					$output .= ")<br />";

					$output .= "</font>";



					//Check if anybody is dead

					if ($defending->hp <= 0)

					{

						$player = ($player->agility >= $enemy->agility)?$attacking:$defending;

						$enemy = ($player->agility >= $enemy->agility)?$defending:$attacking;

						break 2; //Break out of the for and while loop, but not the switch structure

					}

				}

				$battlerounds--;

				if ($battlerounds <= 0)

				{

					break 2; //Break out of for and while loop, battle is over!

				}

			}

			

			for($i = 0;$i < $defending->combo;$i++)

			{

				//Chance to miss?

				$misschance = intval(rand(0, 100));

				if ($misschance <= $defending->miss)

				{

					$output .= $defending->username . " tried to attack " . $attacking->username . " but missed!<br />";

				}

				else

				{

					$damage = rand($defending->mindmg, $defending->maxdmg); //Calculate random damage

					$attacking->hp -= $damage;

					$output .= ($player->username == $defending->username)?"<font color=\"green\">":"<font color=\"red\">";

					$output .= $defending->username . " attacks " . $attacking->username . " for <b>" . $damage . "</b> damage! (";

					$output .= ($attacking->hp > 0)?$attacking->hp . " HP left":"Dead";

					$output .= ")<br />";

					$output .= "</font>";



					//Check if anybody is dead

					if ($attacking->hp <= 0)

					{

						$player = ($player->agility >= $enemy->agility)?$attacking:$defending;

						$enemy = ($player->agility >= $enemy->agility)?$defending:$attacking;

						break 2; //Break out of the for and while loop, but not the switch structure

					}

				}

				$battlerounds--;

				if ($battlerounds <= 0)

				{

					break 2; //Break out of for and while loop, battle is over!

				}

			}

			

			$player = ($player->agility >= $enemy->agility)?$attacking:$defending;

			$enemy = ($player->agility >= $enemy->agility)?$defending:$attacking;

		}

		

		if ($player->hp <= 0)

		{

			//Calculate losses

			$exploss1 = $player->level * 6;

			$exploss2 = (($player->level - $enemy->level) > 0)?($enemy->level - $player->level) * 4:0;

			$exploss = $exploss1 + $exploss2;

			$goldloss = intval(0.2 * $player->gold);

			$goldloss = intval(rand(1, $goldloss));

			

			$output .= "<br /><u>You were defeated by " . $enemy->username . "!</u><br />";

			$output .= "<br />You lost <b>" . $exploss . "</b> EXP and <b>" . $goldloss . "</b> gold.";

			$exploss3 = (($player->exp - $exploss) <= 0)?0:$exploss;

			$goldloss2 = (($player->gold - $goldloss) <= 0)?0:$goldloss;

			//Update player (the loser)

			$query = $db->execute("update `players` set `energy`=?, `exp`=?, `gold`=?, `hp`=0 where `id`=?", array($player->energy - 1, $player->exp - $exploss3, $player->gold - $goldloss2, $player->id));

						

		}

		else if ($enemy->hp <= 0)

		{

			//Calculate losses

			$expwin1 = $enemy->level * 6;

			$expwin2 = (($player->level - $enemy->level) > 0)?$expwin1 - (($player->level - $enemy->level) * 3):$expwin1 + (($player->level - $enemy->level) * 3);

			$expwin2 = ($expwin2 <= 0)?1:$expwin2;

			$expwin3 = round(0.6 * $expwin2);

			$expwin = ceil(rand($expwin3, $expwin2));

			$goldwin = ceil(1.2 * $enemy->gold);

			$goldwin = intval(rand(15, $goldwin));
           
			$output .= "<br /><u>You defeated " . $enemy->username . "!</u><br />";

			$output .= "<br />You won <b>" . $expwin . "</b> EXP and <b>" . $goldwin . "</b> gold.";

			

			if ($expwin + $player->exp >= $player->maxexp) //Player gained a level!

			{

				//Update player, gained a level

				$output .= "<br /><b>You leveled up!</b>";

				$newexp = $expwin + $player->exp - $player->maxexp;

				$query = $db->execute("update `players` set `stat_points`=?, `level`=?, `maxexp`=?, `maxhp`=?, `exp`=?, `hp`=?, `energy`=? where `id`=?", array($player->stat_points + 3, $player->level + 1, ($player->level+1) * 70 - 20, $player->maxhp + 30, $newexp, $player->maxhp + 30, $player->energy - 1, $player->id));

			}

			else

			{

				//Update player

				$query = $db->execute("update `players` set `exp`=?, `gold`=?, `hp`=?, `energy`=? where `id`=?", array($player->exp + $expwin, $player->gold + $goldwin, $player->hp, $player->energy - 1, $player->id));

			}

		}

		else

		{

			$output .= "<br /><u>Both of you were too tired to finish the battle! Nobody won...</u>";

			$query = $db->execute("update `players` set `hp`=?, `energy`=? where `id`=?", array($player->hp, $player->energy - 1, $player->id));

			

		}

		

		$player = check_user($secret_key, $db); //Get new stats

		require_once("templates/themes/" . $setting->theme . "/private_header.php");

		echo $output;

		require_once("templates/themes/" . $setting->theme . "/private_footer.php");

		break;

		

	

	default:

		

		$fromlevel=$player->level-10;

		$tolevel=$player->level+10;

		

		$sql = mysql_query("SELECT * FROM monsters WHERE level>='$fromlevel' AND level<='$tolevel'") or die(mysql_error());		

		if (mysql_num_rows($sql) > 0)//Check if any monsters were found

		{

			require_once("templates/themes/" . $setting->theme . "/private_header.php");
                        echo "<center><image src='images/battle.jpg' alt='Battle' /></center>\n";
			echo "<table width=\"100%\">\n";

		  echo "<tr><th width=\"40%\">Name</th><th width=\"10%\">monster</th><th width=\"20%\">Level</th><th width=\"30%\">Battle</a></th></tr>\n";

		  $bool = 1;

			while ($result = mysql_fetch_array($sql))

			{
				$img='images/monsters/'.$result[image_path];
                                                       
				echo "<tr class=\"row" . $bool . "\">\n";

				echo "<td width=\"40%\">" . $result['username'] . "</td>\n";
                                

                                echo "<td width=\"10%\"><a href=\"javascript:void(0);\" onmouseover=\"overlib('<img src=\'$img\'>', STICKY); return true;\" onmouseout=\"nd(); return 
true;\"><img src='$img' width='64px' height='64px' border='0'></a></td>\n";

				echo "<td width=\"20%\">" . $result['level'] . "</td>\n";

				echo "<td width=\"40%\"><a href=\"monsters.php?act=attack&username=" . $result['username'] ."\" onmouseover=\"drc('Click to attack this monster.','Attack?'); return true;\" onmouseout=\"nd(); return 
true;\">Attack</a></td>\n";

				echo "</tr>\n";

				$bool = ($bool==1)?2:1;

			}

			echo "</table>\n";

			require_once("templates/themes/" . $setting->theme . "/private_footer.php");

		}

		else //Display error message

		{

		require_once("templates/themes/" . $setting->theme . "/private_header.php");

		  echo "<table width=\"100%\">\n";

			echo "<tr>\n";

			echo "<td>No monsters found for your level.</td>\n";

			echo "</tr>\n";

			echo "</table>\n";

			require_once("templates/themes/" . $setting->theme . "/private_footer.php");

		}

		break;

}




?>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<script language="JavaScript" src="overlib.js"><!-- overLIB (c) Erik Bosrup --></script> 
