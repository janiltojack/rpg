<?php
require_once("lib.php");
define("PAGENAME", $lang['page_search']);
$player = check_user($secret_key, $db);
if (isset($_POST ['move'])) {
	if ($player->search == 0) {
		$msg = "You've already reached your search limit.  Try again, tomorrow.";
		$img = "<img src=\"images/angry.gif\" alt=\"no search\" />";
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		?>
		<center><b>Searches left:</b> <?=$player->search?></center>
		<br />
		<form method="post" action="search.php">
		<center>
		<?php echo $img;?>
		</center>
		<br />
		<center>
		<fieldset align="center" style="{width:40%;}">
		<table width="25%" align="center" >
		<tr>
		<td width="8%" align="center">&nbsp;</td>
		<td width="8%" align="center"><input type="submit" name="move" value=" North "/></td>
		<td width="8%" align="center">&nbsp;</td>
		</tr>
		<tr>
		<td width="8%" align="center"><input type="submit" name="move" value=" West "/></td>
		<td width="8%" align="center">&nbsp;</td>
		<td width="8%" align="center"><input type="submit" name="move" value=" East "/></td>
		</tr>
		<tr>
		<td width="8%" align="center">&nbsp;</td>
		<td width="8%" align="center"><input type="submit" name="move" value=" South "/></td>
		<td width="8%" align="center">&nbsp;</td>
		</tr>
		</table>
		</fieldset>
		</center>
		</form>
		<center><?php echo $msg;?></center>
		<?php
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");			
	} else {
		// what you find
		$randnum = rand(1,50);
		$randnum = round($randnum);
		$loss = $player->search - 1;
		switch ($randnum) {
			// finding nada 50% chance
			case ($randnum <= 25):
				$result = $db->execute("UPDATE `players` SET `search`=? WHERE `id`=?", array($loss, $player->id));
				$msg = "You didn't find anything.  Better luck, next time.";
				$img = "<img src=\"images/angry.gif\" alt=\"found nothing\" />";
				require_once("templates/themes/" . $setting->theme . "/private_header.php");
				?>
				<center><b>Searches left:</b> <?=$player->search?></center>
				<br />
				<form method="post" action="search.php">
				<center>
				<?php echo $img;?>
				</center>
				<br />
				<center>
				<fieldset align="center" style="{width:40%;}">
				<table width="25%" align="center" >
				<tr>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" North "/></td>
				<td width="8%" align="center">&nbsp;</td>
				</tr>
				<tr>
				<td width="8%" align="center"><input type="submit" name="move" value=" West "/></td>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" East "/></td>
				</tr>
				<tr>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" South "/></td>
				<td width="8%" align="center">&nbsp;</td>
				</tr>
				</table>
				</fieldset>
				</center>
				</form>
				<center><?php echo $msg;?></center>
				<?php
				require_once("templates/themes/" . $setting->theme . "/private_footer.php");			
				break;
			// finding gold 40% chance
			case ($randnum > 25 && $randnum < 45):
				// how much gold?
				$randgold = round((rand(1,10)) * $player->level);
				$total = $randgold + $player->gold;
				$result = $db->execute("UPDATE `players` SET `search`=?, `gold`=? WHERE `id`=?", array($loss, $total, $player->id));
				$msg = "You've found $".$randgold."!";
				$img = "<img src=\"images/angry.gif\" alt=\"found gold\" />";
				require_once("templates/themes/" . $setting->theme . "/private_header.php");
				?>
				<center><b>Searches left:</b> <?=$player->search?></center>
				<br />
				<form method="post" action="search.php">
				<center>
				<?php echo $img;?>
				</center>
				<br />
				<center>
				<fieldset align="center" style="{width:40%;}">
				<table width="25%" align="center" >
				<tr>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" North "/></td>
				<td width="8%" align="center">&nbsp;</td>
				</tr>
				<tr>
				<td width="8%" align="center"><input type="submit" name="move" value=" West "/></td>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" East "/></td>
				</tr>
				<tr>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" South "/></td>
				<td width="8%" align="center">&nbsp;</td>
				</tr>
				</table>
				</fieldset>
				</center>
				</form>
				<center><?php echo $msg;?></center>
				<?php require_once("templates/themes/" . $setting->theme . "/private_footer.php");			
				break;
			// finding energy 8% chance
			case ($randnum > 45 && $randnum < 50 ):
				// how much energy?
				$randenergy = round((rand(1,5)) * $player->level);
				$total = $randenergy + $player->energy;
				if ($player->maxenergy < $total) {
					$total = $player->maxenergy;
				}
				$result = $db->execute("UPDATE `players` SET `search`=?, `energy`=? WHERE `id`=?", array($loss, $total, $player->id));
	   	 		$msg = "You've found " . $randenergy . " points of energy.  Go get'em, tiger!";
				$img = "<img src=\"images/angry.gif\" alt=\"found energy\" />";
				require_once("templates/themes/" . $setting->theme . "/private_header.php");
				?>
				<center><b>Searches left:</b> <?=$player->search?></center>
				<br />
				<form method="post" action="search.php">
				<center>
				<?php echo $img;?>
				</center>
				<br />
				<center>
				<fieldset align="center" style="{width:40%;}">
				<table width="25%" align="center" >
				<tr>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" North "/></td>
				<td width="8%" align="center">&nbsp;</td>
				</tr>
				<tr>
				<td width="8%" align="center"><input type="submit" name="move" value=" West "/></td>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" East "/></td>
				</tr>
				<tr>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" South "/></td>
				<td width="8%" align="center">&nbsp;</td>
				</tr>
				</table>
				</fieldset>
				</center>
				</form>
				<center><?php echo $msg;?></center>
				<?php require_once("templates/themes/" . $setting->theme . "/private_footer.php");			
				break;
			// finding stat points 2% chance
			case ($randnum == 50):
				// how many stat points?
				$randstats = rand(1,3);
				$total = $randstats + $player->stat_points;
				$result = $db->execute("UPDATE `players` SET `search`=?, `stat_points`=? WHERE `id`=?", array($loss, $total, $player->id));
				$msg = "You lucky so-and-so.  You've found " . $randstats  . " Stat Point(s).  Enjoy.";
				$img = "<img src=\"images/angry.gif\" alt=\"found stats\" />";
				require_once("templates/themes/" . $setting->theme . "/private_header.php");
				?>
				<center><b>Searches left:</b> <?=$player->search?></center>
				<br />
				<form method="post" action="search.php">
				<center>
				<?php echo $img;?>
				</center>
				<br />
				<center>
				<fieldset align="center" style="{width:40%;}">
				<table width="25%" align="center" >
				<tr>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" North "/></td>
				<td width="8%" align="center">&nbsp;</td>
				</tr>
				<tr>
				<td width="8%" align="center"><input type="submit" name="move" value=" West "/></td>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" East "/></td>
				</tr>
				<tr>
				<td width="8%" align="center">&nbsp;</td>
				<td width="8%" align="center"><input type="submit" name="move" value=" South "/></td>
				<td width="8%" align="center">&nbsp;</td>
				</tr>
				</table>
				</fieldset>
				</center>
				</form>
				<center><?php echo $msg;?></center>
				<?php require_once("templates/themes/" . $setting->theme . "/private_footer.php");
				break;
		}
	}
} else {
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	$default_img = "<img src=\"images/angry.gif\" alt=\"default search\" />";
	?>
	<center><b>Searches left:</b> <?=$player->search?></center>
	<br />
	<form method="post" action="search.php">
	<center>
	<?php echo $default_img; ?>
	</center>
	<br />
	<center>
	<fieldset align="center" style="{width:40%;}">
	<table width="25%" align="center" >
	<tr>
	<td width="8%" align="center">&nbsp;</td>
	<td width="8%" align="center"><input type="submit" name="move" value=" North "/></td>
	<td width="8%" align="center">&nbsp;</td>
	</tr>
	<tr>
	<td width="8%" align="center"><input type="submit" name="move" value=" West "/></td>
	<td width="8%" align="center">&nbsp;</td>
	<td width="8%" align="center"><input type="submit" name="move" value=" East "/></td>
	</tr>
	<tr>
	<td width="8%" align="center">&nbsp;</td>
	<td width="8%" align="center"><input type="submit" name="move" value=" South "/></td>
	<td width="8%" align="center">&nbsp;</td>
	</tr>
	</table>
	</fieldset>
	</center>
	</form>
	<center><?php echo $msg;?></center>
	<?php
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
}
?>