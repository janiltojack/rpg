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
define("PAGENAME", "Items Market Sell");
$player = check_user($secret_key, $db);

// Pagination variables
$limit = (!$_GET['limit'])?30:intval($_GET['limit']); //Use user-selected limit of players to list
$begin = (!$_GET['begin'])?$player->id-intval($limit / 2):intval($_GET['begin']); //List players with the current player in the middle of the list
$begin = ($begin < 0)?0:$begin; //Can't list negative players :)
$total_items = $db->getone("select count(market_id) as `count` from `market`");
$begin = ($begin >= $total_items)?$total_items - $limit:$begin; //Can't list players don't don't exist yet either
$begin = ($begin < 0)?0:$begin; //Can't list negative players :)
$lastpage = (($total_items - $limit) < 0)?0:$total_items - $limit; //Get the starting point if the user has browsed to the last page
// End of pagination variables

switch($_GET['act']) {
	
case "sell": 
		
	$item=$_GET['item'];
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
	echo '<center>So you want to sell some of your inventory. Great, feel out the quantity and sell price. Then click the &quot;Sell&quot; button, but keep in mind, this market doesnt run for free, we get 10% commission (or at least 1 dollar) on all items you list and we get it up front.  So you will have to have the coinage to sell your items.<br />';
	echo "<form method=\"post\" action=\"market_sell.php?act=confirm&item=" . $item . "\" >";
	echo 'You would like to sell:<br /><br />';
	echo item_name($item, $db) . "<br><br />";
	echo '<input type="hidden" name="act" value="confirm">';
	echo "<input type=\"hidden\" name=\"item\" value=\"" . $item . "\">";
	echo 'Asking Sell Price: <input type="text" name="price"><br /><br />';
	echo '<input type="submit" value="Submit">';
	echo '</form></center>';
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	//end of sell action
		
break;

case "confirm": 
	
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	$item=stripslashes($_POST['item']);
	$price=stripslashes($_POST['price']);
	$fee=floor($price/10);		
	if ($fee<1){$fee=1;}	
	if($price<=0) {
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo '<center>Sorry, We do not allow you to give things away.</center>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit; 
	} else {	
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo "<center>Please confirm that you want to sell " . item_name($item, $db) . " for " . $price . ".  You will be charged " . $fee . " dollars for this listing.";
       	echo "<form method=\"post\" action=\"market_sell.php?act=list&item=" . $item . "\">";
		echo "<input type=\"hidden\" name=\"item\" value=\"" . $item . "\">";
		echo "<input type=\"hidden\" name=\"price\" value=\"" . $price . "\">";
		echo "<input type=\"hidden\" name=\"fee\" value=\"" . $fee . "\">";
		echo '<input type="submit" name="list" value="Yes, I am sure!">';
		echo '</form></center>';
	}
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");

break;

case "list": 

	$item=stripslashes($_POST['item']);
	$query = $db->execute("select * from items where id=?", array($item));
	$specificitem=$query->fetchrow();
	$item_id =  $specificitem['item_id'];
	$item2=item_name($_POST['item'], $db);
	$price=stripslashes($_POST['price']);
	$total=$price;
	$fee=stripslashes($_POST['fee']);			
	//check to see if player can afford to list
	if($player->gold<$fee) {
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo '<center>Sorry, you can afford to list that item.</center>';	
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	} else {	
		//add item to market 
		$insert['item_name'] = $item2;
		$insert['item_id'] = $item_id;
		$insert['qty'] = 1;
		$insert['price']= $price;
		$insert['total_cost']= $price;
		$insert['seller']= $player->username;
		$insert['seller_id']= $player->id;
		$query2 = $db->autoexecute('market', $insert, 'INSERT');
		//remove fee from player
		$query = $db->execute("update `players` set `gold`=? where `id`=?", array($player->gold - $fee, $player->id));
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		$query - $db->execute("delete from items where id=$item");
		echo '<center>Your item is now available in the market.  Thanks for using your local marketplace.</center>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	}

break;
	
default: 

	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	$query = $db->execute("select blueprint_items.name, blueprint_items.type, items.id from blueprint_items, items where blueprint_items.id=items.item_id and items.player_id=? order by blueprint_items.type asc", array($player->id));
	if ($query->recordcount() == 0) {
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo "<center><b>You have no items to sell.</b></center>";
	} else {
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo '<center>Which items would you like to sell? </center><br />';
		echo "<table id=\"forum-list\" width=\"100%\">";
		echo "<tr>";
		echo "<th align=\"left\"><b>Item</b></th>";
		echo "<th align=\"left\"><b>Type</b></th>";
		echo "<th align=\"left\"><b>Action</b></th>";
		echo "</tr>";
 		while($youritems = $query->fetchrow()) { 
	 		echo '<tr>';
	 		echo "<td>" . $youritems['name'] . "</td>";
     		echo "<td>" . $youritems['type'] . "</td>";
	 		echo "<td><a href=\"market_sell.php?act=sell&item=" . $youritems['id'] . "\">sell</a></td>";
	 		echo '</tr>'; 		
		}
		echo '</table>';
	}
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");

break;

}
?>