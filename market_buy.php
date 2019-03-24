<?php
/****************************************/
/*             ezRPG script             */
/*           Written by Zeggy           */
/*    http://code.google.com/p/ezrpg    */
/*      http://www.bbgamezone.com/      */
/*         Mod: market_buy.php          */
/*       Written by MasterTester        */
/* http://www.yourcounty4sale.com/king/ */
/*        Cleaned by Bogatabeav         */
/*  	   http://sunofloki.com         */
/****************************************/
include("lib.php");
define("PAGENAME", "Items Market Buy");
$player = check_user($secret_key, $db);

switch($_GET['act']) {
	
case "confirm":

	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	$item_name=$_POST['item'];
	$total_cost=$_POST['total'];
	$seller_id=$_POST['seller_id'];
	$market_id=$_POST['market_id'];
	$query=$db->execute('select * from market where market_id=?', array($market_id));
	$itemname=$query->fetchrow();
	$query2=$db->execute('select * from blueprint_items where name like ?', array($item_name));
	$newitem=$query2->fetchrow();
	$newitemid=$newitem['id'];		
	//Pull info from blueprint items and then compare them to items list to get count.
	if ($query->recordcount() == 0) {
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo '<center><br /><b>Sorry, there are no items listed.</b></center>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	//Do you have the money?
	} elseif ($total_cost > $player->gold) {
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo '<center><br /><b>Sorry, but you can not afford that item.</b></center>'; 
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	//Is it already sold?
	} elseif ($itemname['sold'] == 't') {
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo '<center>Sorry, that item is no longer available.</center>'; 
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	//Code to remove gold from buyer.		
	} else {
		$query_buyer_gold = $db->execute("update `players` set `gold`=? where `id`=?", array($player->gold - $total_cost, $player->id));
		//Code to add gold to seller.
		$query_seller =$db->execute("select `bank` from `players` where `id`=?", array($seller_id));
		$query_2 = $db->execute("update `players` set `bank`=? where `id`=?", array($seller['bank'] + $total_cost, $seller_id));
		//Code to add log to seller.
		$msg = "You sold an item on the market. <a href=\"profile.php?id=" . $player->username . "\">" . $player->username . "</a> purchased your " . $item_name . " and you made " . $total_cost . " gold.";
		addlog($seller_id, $msg, $db);
		//Code to mark item as sold.
		$query_sold=$db->execute("update `market` set `sold`=? where `market_id`=?", array(t, $market_id)); 				
		//Add to items table for buyer		
		$insert['player_id'] = $player->id;
		$insert['item_id'] = $newitemid;
		$insert['template'] = $newitemtemplate;
		$buyitem = $db->autoexecute('items', $insert, 'INSERT');
	}
	if ($query_buyer_gold && $query_2 && $query_sold) {
 		$player = check_user($secret_key, $db); //Get new user stats
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo '<center>Thank you for your purchase.  Enjoy your item.</center>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	}
	
break;
	
case "buy": 
	
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
	$market_id=$_GET['item'];
	$query_market = $db->execute("select * from `market` where `market_id`=? and sold='f'", array($market_id));
	while($market = $query_market->fetchrow()) {
		//Pull info from blueprint items and then compare them to items list to get count.
		$query = $db->execute("select * from blueprint_items where blueprint_items.name=? order by blueprint_items.name asc", array($market['item_name']));
		//saves them the trouble of finding out on confirm
		if ($query->recordcount() == 0) {
			echo '<br /><b>That item is no longer on the market.</b>';
		} else {
			while($temp_item = $query->fetchrow()) { 
				//saves them the trouble of finding out on confirm
				if ($market['total_cost'] > $player->gold) {
	 				echo '<center>Sorry, but you can not afford that item.</center>'; 
					require_once("templates/themes/" . $setting->theme . "/private_footer.php");
				} 
			echo '<center>Please confirm that you would like to make this purchase?';	
			echo '<form method="POST" action="market_buy.php?act=confirm">';
			echo 'You would like to buy:<br /><br />';
			echo $market['item_name'] . " for " . $market['total_cost'] . " gold.<br /><br /><br />";
			echo '<input type="hidden" name="act" value="confirm">';
			echo "<input type=\"hidden\" name=\"qty\" value=\"" . $market['qty'] . "\">";
			echo "<input type=\"hidden\" name=\"market_id\" value=\"" . $market['market_id'] . "\">";
			echo "<input type=\"hidden\" name=\"item\" value=\"" . $market['item_name'] . "\">";
			echo "<input type=\"hidden\" name=\"total\" value=\"" . $market['total_cost'] . "\">";
			echo "<input type=\"hidden\" name=\"seller_id\" value=\"" . $market['seller_id'] . "\">";
			echo '<input type="submit" value="Yes, I want to make this purchase.">';
			echo '</form></center>';
			}
		}
	}
	
break;

}

require_once("templates/themes/" . $setting->theme . "/private_footer.php");
?>