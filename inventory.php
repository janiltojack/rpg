<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_inventory']);
$player = check_user($secret_key, $db);

if ($_GET['id']) {
	$query = $db->execute("select `status`, `item_id` from `items` where `id`=? and `player_id`=?", array($_GET['id'], $player->id));
	if ($query->recordcount() == 1) {
		$item = $query->fetchrow();
		switch($item['status']) {
			case "unequipped": //User wants to equip item
				//$itemtype = $db->getone("select `type` from `blueprint_items` where `id`=?", array($item['item_id']));
				//Check if another item is already equipped
				$unequip = $db->getone("select items.id from `items`, `blueprint_items` where items.item_id = blueprint_items.id and blueprint_items.type=(select `type` from `blueprint_items` where `id`=?) and items.player_id=? and `status`='equipped'", array($item['item_id'], $player->id));
				if ($unequip) //If so, then unequip it (only one item may be equipped at any one time)
				{
					$query = $db->execute("update `items` set `status`='unequipped' where `id`=?", array($unequip));
				}
				//Equip the selected item
				$query = $db->execute("update `items` set `status`='equipped' where `id`=?", array($_GET['id']));
				break;
			case "equipped": //User wants to unequip item
				$query = $db->execute("update `items` set `status`='unequipped' where `id`=?", array($_GET['id']));
				break;
			default: //Set status to unequipped, in case the item had no status when it was inserted into db
				$query = $db->execute("update `items` set `status`='unequipped' where `id`=?", array($_GET['id']));
				break;
		}
	}
}
require_once("templates/themes/" . $setting->theme . "/private_header.php");
?>

<br />
<?php
$query = $db->execute("select items.id, items.item_id, items.status, blueprint_items.type, blueprint_items.name, blueprint_items.effectiveness, blueprint_items.description from `items`, `blueprint_items` where blueprint_items.id=items.item_id and items.player_id=? and blueprint_items.type='weapon' order by items.status asc", array($player->id));
if ($query->recordcount() == 0) {
	echo "<br />" . $lang['error_no_weapons'];
} else {
	echo "<table id=\"forum-list\" width=\"100%\">\n";
	echo "<tr><th colspan=\"3\">Weapons</th></tr>";
	while($item = $query->fetchrow()) {
		echo "<tr>";
		echo "<td width=\"15%\"><b>" . $item['name'] . "</b></legend>\n</td>";
		echo "<td width=\"60%\">";
		echo $item['description'] . "\n<br />";
		echo "<b>" . $lang['keyword_effectiveness'] . ":</b> " . $item['effectiveness'] . "\n";
		echo "</td><td width=\"25%\">";
		echo "<a href=\"shop.php?act=sell&id=" . $item['id'] . "\">" . $lang['keyword_sell'] . "</a><br />";
		echo "<a href=\"market_sell.php?act=sell&item=" . $item['id'] . "\">" . $lang['keyword_msell'] . "</a><br />";
		echo "<a href=\"inventory.php?id=" . $item['id'] . "\">";
		echo ($item['status'] == "equipped")?$lang['keyword_unequip']:$lang['keyword_equip'];
		echo "</a>";
		echo "</td></tr>\n";
	}
	echo "</table>";
}
?>
<br /><br />
<?php
$query = $db->execute("select items.id, items.item_id, items.status, blueprint_items.type, blueprint_items.name, blueprint_items.effectiveness, blueprint_items.description from `items`, `blueprint_items` where blueprint_items.id=items.item_id and items.player_id=? and blueprint_items.type='armour' order by items.status asc", array($player->id));
if ($query->recordcount() == 0) {
	echo "<br />" . $lang['error_no_armour'];
} else {
	echo "<table id=\"forum-list\" width=\"100%\">\n";
	echo "<tr><th colspan=\"3\">Armor</th></tr>";
	while($item = $query->fetchrow()) {
		echo "<tr>";
		echo "<td width=\"15%\"><b>" . $item['name'] . "</b></legend>\n</td>";
		echo "<td width=\"60%\">";
		echo $item['description'] . "\n<br />";
		echo "<b>" . $lang['keyword_effectiveness'] . ":</b> " . $item['effectiveness'] . "\n";
		echo "</td><td width=\"25%\">";
		echo "<a href=\"shop.php?act=sell&id=" . $item['id'] . "\">" . $lang['keyword_sell'] . "</a><br />";
		echo "<a href=\"market_sell.php?act=sell&item=" . $item['id'] . "\">" . $lang['keyword_msell'] . "</a><br />";
		echo "<a href=\"inventory.php?id=" . $item['id'] . "\">";
		echo ($item['status'] == "equipped")?$lang['keyword_unequip']:$lang['keyword_equip'];
		echo "</a>";
		echo "</td></tr>\n";
	}
	echo "</table>";
}
?>
<br /><br />
<?php
require_once("templates/themes/" . $setting->theme . "/private_footer.php");
?>