<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_shop']);
$player = check_user($secret_key, $db);

switch($_GET['act']) {
	case "buy":
		if (!$_GET['id']) {
			header("Location: shop.php");
			break;
		}
		//Select the item from the database
		$query = $db->execute("select `id`, `name`, `price` from `blueprint_items` where `id`=?", array($_GET['id']));
		//Invalid item (it doesn't exist)
		if ($query->recordcount() == 0) {
			header("Location: shop.php");
			break;
		}
		$item = $query->fetchrow();
		if ($item['price'] > $player->gold) {
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo "<b>" . $lang['npc_shop'] . ":</b><br />\n";
			echo "<i>" . $lang['error_cannot_afford'] . "</i><br /><br />\n";
			echo "<a href=\"inventory.php\">" . $lang['msg_return_inventory'] . "</a> | <a href=\"shop.php\">" . $lang['msg_return_shop'] . "</a>";
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		}
		$query1 = $db->execute("update `players` set `gold`=? where `id`=?", array($player->gold - $item['price'], $player->id));
		$insert['player_id'] = $player->id;
		$insert['item_id'] = $item['id'];
		$query2 = $db->autoexecute('items', $insert, 'INSERT');
		if ($query1 && $query2) {
			$player = check_user($secret_key, $db); //Get new user stats	
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo "<b>" . $lang['npc_shop'] . ":</b><br />\n";
			echo "<i>" . sprintf($lang['msg_bought_item'], $item['name']) . "</i><br /><br />\n";
			echo "<a href=\"inventory.php\">" . $lang['msg_return_inventory'] . "</a> | <a href=\"shop.php\">" . $lang['msg_return_shop'] . "</a>";
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		} else {
			//Error logging here
		}
		break;
	case "sell":
		if (!$_GET['id']) {
			header("Location: shop.php");
			break;
		}
		//Select the item from the database
		$query = $db->execute("select items.id, blueprint_items.name, blueprint_items.price from `blueprint_items`, `items` where items.item_id=blueprint_items.id and items.player_id=? and items.id=?", array($player->id, $_GET['id']));
		//Either item doesn't exist, or item doesn't belong to user
		if ($query->recordcount() == 0) {
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo $lang['error_no_item'];
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		}
		$sell = $query->fetchrow(); //Get item info
		//Check to make sure clicking Sell wasn't an accident
		if (!$_POST['sure']) {
			require_once("templates/themes/" . $setting->theme . "/private_header.php");
			echo sprintf($lang['msg_verify_sell'], $sell['name'], floor($sell['price'] / 2)) . "<br /><br />\n";
			echo "<form method=\"post\" action=\"shop.php?act=sell&id=" . $sell['id'] . "\">\n";
			echo "<input type=\"submit\" name=\"sure\" value=\"" . $lang['msg_sure'] . "\" />\n";
			echo "</form>\n";
			require_once("templates/themes/" . $setting->theme . "/private_footer.php");
			break;
		}
		//Delete item from database, add gold to player's account
		$query = $db->execute("delete from `items` where `id`=?", array($sell['id']));
		$query = $db->execute("update `players` set `gold`=? where `id`=?", array($player->gold + floor($sell['price']/2), $player->id));	
		$player = check_user($secret_key, $db); //Get updated user info
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo sprintf($lang['msg_sell_success'], $sell['name'], floor($sell['price']/2)) . "<br /><br />\n";
		echo "<a href=\"inventory.php\">" . $lang['msg_return_inventory'] . "</a> | <a href=\"shop.php\">" . $lang['msg_return_shop'] . "</a>";
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		break;
	case "weapon":
		//Check in case somebody entered 0
		$_GET['fromprice'] = ($_GET['fromprice'] == 0)?"":$_GET['fromprice'];
		$_GET['toprice'] = ($_GET['toprice'] == 0)?"":$_GET['toprice'];
		$_GET['fromeffect'] = ($_GET['fromeffect'] == 0)?"":$_GET['fromeffect'];
		$_GET['toeffect'] = ($_GET['toeffect'] == 0)?"":$_GET['toeffect'];	
		//Construct query
		$query = "select `id`, `name`, `description`, `price`, `effectiveness` from `blueprint_items` where ";
		$query .= ($_GET['name'] != "")?"`name` LIKE  ? and ":"";
		$query .= ($_GET['fromprice'] != "")?"`price` >= ? and ":"";
		$query .= ($_GET['toprice'] != "")?"`price` <= ? and ":"";
		$query .= ($_GET['fromeffect'] != "")?"`effectiveness` >= ? and ":"";
		$query .= ($_GET['toeffect'] != "")?"`effectiveness` <= ? and ":"";	
		$query .= "`type`='weapon' order by `price` asc";	
		//Construct values array for adoDB
		$values = array();
		if ($_GET['name'] != "") {
			array_push($values, "%".trim($_GET['name'])."%");
		}
		if ($_GET['fromprice']) {
			array_push($values, intval($_GET['fromprice']));
		}
		if ($_GET['toprice']) {
			array_push($values, intval($_GET['toprice']));
		}
		if ($_GET['fromeffect']) {
			array_push($values, intval($_GET['fromeffect']));
		}
		if ($_GET['toeffect']) {
			array_push($values, intval($_GET['toeffect']));
		}
		$query = $db->execute($query, $values); //Search!
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo "<b>" . $lang['npc_shop'] . ":</b>\n";
		echo "<fieldset>";
		echo "<legend><i>" . $lang['msg_shop_greeting'] . "</i></legend>";
		echo "<form method=\"get\" action=\"shop.php\">\n";
		echo "<table id=\"form-list\" width=\"100%\">\n";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_name'] . ":</td>\n";
		echo "<td width=\"60%\"><input type=\"text\" name=\"name\" value=\"" . stripslashes($_GET['name']) . "\" /></td>\n";
		echo "</td>\n</tr>";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_price'] . ":</td>\n";
		echo "<td width=\"60%\"><input type=\"text\" name=\"fromprice\" size=\"4\" value=\"" . stripslashes($_GET['fromprice']) . "\" /> - <input type=\"text\" name=\"toprice\" size=\"4\" value=\"" . stripslashes($_GET['toprice']) . "\" /></td>\n";
		echo "</td>\n</tr>";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_effectiveness'] . ":</td>\n";
		echo "<td width=\"60%\"><input type=\"text\" name=\"fromeffect\" size=\"4\" value=\"" . stripslashes($_GET['fromeffect']) . "\" /> - <input type=\"text\" name=\"toeffect\" size=\"4\" value=\"" . stripslashes($_GET['toeffect']) . "\" /></td>\n";
		echo "</td>\n</tr>";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_type'] . ":</td>\n";
		echo "<td width=\"60%\"><select name=\"act\" size=\"2\">\n";
		echo "<option value=\"weapon\" selected=\"selected\">" . $lang['keyword_weapons'] . "</option>\n";
		echo "<option value=\"armour\">" . $lang['keyword_armour'] . "</option>\n";
		echo "</select></td>\n</tr>\n";
		echo "<tr>\n<td></td>";
		echo "<td><input type=\"submit\" value=\"" . $lang['keyword_search'] . "\" /></td>\n</tr>";
		echo "</table>";
		echo "</form>\n";
		echo "</fieldset>";
		echo "<br />";
		echo "<fieldset><legend><i>" . $lang['msg_shop_weapon'] . "</i></legend>";
		if ($query->recordcount() == 0) {
			echo $lang['error_item_search'];
		} else {
			while ($item = $query->fetchrow()) {
				echo "<table id=\"item-list\" width=\"100%\">\n";
				echo "<tr>";
				echo "<td width=\"25%\"><b>" . $item['name'] . "</b></td>\n";
				echo "<td>" . $item['description'] . "\n<br />";
				echo "<b>" . $lang['keyword_effectiveness'] . ":</b> " . $item['effectiveness'] . "\n";
				echo "</td><td width=\"20%\">";
				echo "<b>" . $lang['keyword_price'] . ":</b> " . $item['price'] . "<br />";
				echo "<a href=\"shop.php?act=buy&id=" . $item['id'] . "\">" . $lang['keyword_buy'] . "</a><br />";
				echo "</td></tr>\n";
				echo "</table>";
				echo "<br />";
			}
		}
		echo "</fieldset>";
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		break;
	case "armour":
		//Check in case somebody entered 0
		$_GET['fromprice'] = ($_GET['fromprice'] == 0)?"":$_GET['fromprice'];
		$_GET['toprice'] = ($_GET['toprice'] == 0)?"":$_GET['toprice'];
		$_GET['fromeffect'] = ($_GET['fromeffect'] == 0)?"":$_GET['fromeffect'];
		$_GET['toeffect'] = ($_GET['toeffect'] == 0)?"":$_GET['toeffect'];
		//Construct query
		$query = "select `id`, `name`, `description`, `price`, `effectiveness` from `blueprint_items` where ";
		$query .= ($_GET['name'] != "")?"`name` LIKE  ? and ":"";
		$query .= ($_GET['fromprice'] != "")?"`price` >= ? and ":"";
		$query .= ($_GET['toprice'] != "")?"`price` <= ? and ":"";
		$query .= ($_GET['fromeffect'] != "")?"`effectiveness` >= ? and ":"";
		$query .= ($_GET['toeffect'] != "")?"`effectiveness` <= ? and ":"";
		$query .= "`type`='armour' order by `price` asc";
		//Construct values array for adoDB
		$values = array();
		if ($_GET['name'] != "") {
			array_push($values, "%".trim($_GET['name'])."%");
		}
		if ($_GET['fromprice']) {
			array_push($values, intval($_GET['fromprice']));
		}
		if ($_GET['toprice']) {
			array_push($values, intval($_GET['toprice']));
		}
		if ($_GET['fromeffect']) {
			array_push($values, intval($_GET['fromeffect']));
		}
		if ($_GET['toeffect']) {
			array_push($values, intval($_GET['toeffect']));
		}
		$query = $db->execute($query, $values); //Search!
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo "<b>" . $lang['npc_shop'] . ":</b>\n";
		echo "<fieldset>";
		echo "<legend><i>" . $lang['msg_shop_greeting'] . "</i></legend>";
		echo "<form method=\"get\" action=\"shop.php\">\n";
		echo "<table id=\"form-list\" width=\"100%\">\n";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_name'] . ":</td>\n";
		echo "<td width=\"60%\"><input type=\"text\" name=\"name\" value=\"" . stripslashes($_GET['name']) . "\" /></td>\n";
		echo "</td>\n</tr>";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_price'] . ":</td>\n";
		echo "<td width=\"60%\"><input type=\"text\" name=\"fromprice\" size=\"4\" value=\"" . stripslashes($_GET['fromprice']) . "\" /> - <input type=\"text\" name=\"toprice\" size=\"4\" value=\"" . stripslashes($_GET['toprice']) . "\" /></td>\n";
		echo "</td>\n</tr>";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_effectiveness'] . ":</td>\n";
		echo "<td width=\"60%\"><input type=\"text\" name=\"fromeffect\" size=\"4\" value=\"" . stripslashes($_GET['fromeffect']) . "\" /> - <input type=\"text\" name=\"toeffect\" size=\"4\" value=\"" . stripslashes($_GET['toeffect']) . "\" /></td>\n";
		echo "</td>\n</tr>";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_type'] . ":</td>\n";
		echo "<td width=\"60%\"><select name=\"act\" size=\"2\">\n";
		echo "<option value=\"weapon\">" . $lang['keyword_weapons'] . "</option>\n";
		echo "<option value=\"armour\" selected=\"selected\">" . $lang['keyword_armour'] . "</option>\n";
		echo "</select></td>\n</tr>\n";
		echo "<tr>\n<td></td>";
		echo "<td><input type=\"submit\" value=\"" . $lang['keyword_search'] . "\" /></td>\n</tr>";
		echo "</table>";
		echo "</form>\n";
		echo "</fieldset>";
		echo "<br />";
		echo "<fieldset><legend><i>" . $lang['msg_shop_armour'] . "</i></legend>";
		if ($query->recordcount() == 0) {
			echo $lang['error_item_search'];
		} else {
			while ($item = $query->fetchrow()) {
				echo "<table id=\"item-list\" width=\"100%\">\n";
				echo "<tr>";
				echo "<td width=\"25%\"><b>" . $item['name'] . "</b></td>\n";
				echo "<td>" . $item['description'] . "\n<br />";
				echo "<b>" . $lang['keyword_effectiveness'] . ":</b> " . $item['effectiveness'] . "\n";
				echo "</td><td width=\"20%\">";
				echo "<b>" . $lang['keyword_price'] . ":</b> " . $item['price'] . "<br />";
				echo "<a href=\"shop.php?act=buy&id=" . $item['id'] . "\">" . $lang['keyword_buy'] . "</a><br />";
				echo "</td></tr>\n";
				echo "</table>";
				echo "<br />";
			}
		}
		echo "</fieldset>";
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		break;
	default:
		//Show search form
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo "<b>" . $lang['npc_shop'] . ":</b>\n";
		echo "<fieldset>";
		echo "<legend><i>" . $lang['msg_shop_greeting'] . "</i></legend>";
		echo "<form method=\"get\" action=\"shop.php\">\n";
		echo "<table id=\"form-list\" width=\"100%\">\n";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_name'] . ":</td>\n";
		echo "<td width=\"60%\"><input type=\"text\" name=\"name\" /></td>\n";
		echo "</td>\n</tr>";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_price'] . ":</td>\n";
		echo "<td width=\"60%\"><input type=\"text\" name=\"fromprice\" size=\"4\" /> - <input type=\"text\" name=\"toprice\" size=\"4\" /></td>\n";
		echo "</td>\n</tr>";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_effectiveness'] . ":</td>\n";
		echo "<td width=\"60%\"><input type=\"text\" name=\"fromeffect\" size=\"4\" /> - <input type=\"text\" name=\"toeffect\" size=\"4\" /></td>\n";
		echo "</td>\n</tr>";
		echo "<tr>\n<td width=\"40%\">" . $lang['keyword_type'] . ":</td>\n";
		echo "<td width=\"60%\"><select name=\"act\" size=\"2\">\n";
		echo "<option value=\"weapon\" selected=\"selected\">" . $lang['keyword_weapons'] . "</option>\n";
		echo "<option value=\"armour\">" . $lang['keyword_armour'] . "</option>\n";
		echo "</select></td>\n</tr>\n";
		echo "<tr>\n<td></td>";
		echo "<td><input type=\"submit\" value=\"" . $lang['keyword_search'] . "\" /></td>\n</tr>";
		echo "</table>";
		echo "</form>\n";
		echo "</fieldset>";
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		break;
}
?>