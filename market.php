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
define("PAGENAME", "Items Market");
$player = check_user($secret_key, $db);

// Pagination variables
$limit = (!$_GET['limit'])?30:intval($_GET['limit']); //Use user-selected limit of players to list
$begin = (!$_GET['begin'])?$player->id-intval($limit / 2):intval($_GET['begin']); //List players with the current player in the middle of the list
$begin = ($begin < 0)?0:$begin; //Can't list negative players :)
$total_items = $db->getone("select count(market_id) as `count` from `market` where sold='f'");
$begin = ($begin >= $total_items)?$total_items - $limit:$begin; //Can't list players don't don't exist yet either
$begin = ($begin < 0)?0:$begin; //Can't list negative players :)
$lastpage = (($total_items - $limit) < 0)?0:$total_items - $limit; //Get the starting point if the user has browsed to the last page
$order=$_GET['order'];
$abc=$_GET['abc'];
if($order=="item"){$orderby="item_name";}
elseif($order=="price"){$orderby="price";}
elseif($order=="qty"){$orderby="qty";}
elseif($order=="cost"){$orderby="total_cost";}
elseif($order=="seller"){$orderby="seller";}
else{$orderby="item_id"; $abc="asc";}
// End of pagination variables

switch($_GET['act']) {
	
case "remove": 
		
	$market_id=$_GET['item'];
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
	echo '<center>Are you sure you want to remove your listing?<br /><br />';
	echo "<a href=\"market.php?act=confirm&item=" . $market_id . "\"><b>YES</b></a>&nbsp;&nbsp;&nbsp;or&nbsp;&nbsp;&nbsp;<a href=\"market.php\"><b>NO</b></a></center>";
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	
break;
		
//for looking at your own items	
case "ownitems": 

	$limit = (!$_GET['limit'])?30:intval($_GET['limit']); 
	$begin = (!$_GET['begin'])?$player->id-intval($limit / 2):intval($_GET['begin']); 
	$begin = ($begin < 0)?0:$begin; //Can't list negative players :)
	$total_items = $db->getone("select count(*) as `count` from `market` where `seller_id=?", array($player->id));
	$begin = ($begin >= $total_items)?$total_items - $limit:$begin;
	$begin = ($begin < 0)?0:$begin; 
	$lastpage = (($total_items - $limit) < 0)?0:$total_items - $limit; 
	$order=$_GET['order'];
	$abc=$_GET['abc'];
	if($order=="item"){$orderby="item_name";}
	elseif($order=="price"){$orderby="price";}
	else{$orderby="item_id"; $abc="asc";}
	$query=$db->execute("select * from market where `sold`='f' and seller_id=?", array($player->id));
	if ($query->recordcount() == 0) {
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
		echo '<center>You have no items for sell.</center>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	} else {
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table>';
		echo '<table id="forum-list" width="100%"><tr>';
?>
		<th align="left"><b><a href="market.php?act=ownitems&order=item&abc=<?php  if($abc=='desc'){echo 'asc';}elseif($abc=='asc'){echo 'desc';}else {echo 'asc';}?>">Item</a></b></td>
		<th align="left"><b><a href="market.php?act=ownitems&order=price&abc=<?php  if($abc=='desc'){echo 'asc';}elseif($abc=='asc'){echo 'desc';}else {echo 'asc';}?>">Asking Price</a></b></td>
<?php
		echo '<th align=\"left\"><b>Action</b></td></tr>';
		//Query the market db
		$query_market = $db->execute("select `market_id`, `item_id`, `item_name`, `qty`, `price`, `total_cost`,`seller_id`,`seller`, `sold` from `market` where `sold`='f' order by $orderby $abc limit ?,?", array($begin, $limit));
		while($market = $query_market->fetchrow()) {
			echo '<tr><br />';
			echo "<td>" . $market['item_name'] . "</td>";
			echo "<td>" . $market['price'] . "</td>";
			echo "<td><a href=\"market.php?act=remove&item=" . $market['market_id'] . "\">Remove</a></td></tr>\n";
		}
		echo '</table>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	}
	
break;

case "confirm": 

	$market_id=$_GET['item'];
	$query=$db->execute("select * from market where market_id=?", array($market_id));
	$itemname=$query->fetchrow();
	$query2=$db->execute("select * from blueprint_items where id like ?", array($itemname['item_id']));
	$newitem=$query2->fetchrow();
	$newitemid=$newitem['id'];
	$newitemtemplate=$newitem['comptemplate'];
	//Prevents infinite items on refresh
	if ($itemname['sold'] == "t") {
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
	 	echo '<center>That item has already been removed.</center>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	 	exit;
	 } 
	//writes item back to items table for player
	$insert['player_id'] = $player->id;
	$insert['item_id'] = $newitemid;
	$insert['template'] = $newitemtemplate;
	$removeitem = $db->autoexecute('items', $insert, 'INSERT');
	//marks item as sold instead of deleting it all together, for a sales record.
	$query_sold=$db->execute("update `market` set `sold`=? where `market_id`=?", array(t, $market_id));
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table><br />';
	echo '<center>Your item listing has been removed.</center>';
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");

break;

default:
	
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo '<table id="forum-list-b"><tr><td><a href="market.php">The Market</a> / <a href="market.php?act=ownitems">Your Market Items</a> / <a href="market_sell.php">Your Inventory Items</a></td></tr></table>';
	if ($total_items == 0) {
		echo '<br /><center>There are no items for sell.</center>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	} else {
?>
		<table id="forum-list" width="100%"><tr>
		<th align=left><b><a href="market.php?order=item&abc=<?php  if($abc=="desc"){echo "asc";}elseif($abc=="asc"){echo "desc";}else {echo "asc";}?>">Item</a></b></td>
		<th align=left><b><a href="market.php?order=price&abc=<?php  if($abc=="desc"){echo "asc";}elseif($abc=="asc"){echo "desc";}else {echo "asc";}?>">Asking Price</a></b></td>
		<th align=left><b><a href="market.php?order=seller&abc=<?php  if($abc=="desc"){echo "asc";}elseif($abc=="asc"){echo "desc";}else {echo "asc";}?>">Seller</a></b></td>
		<th align=left><b>Action</b></td></tr>
<?php
		//Query the market db
		$query_market = $db->execute("select `market_id`, `item_id`, `item_name`, `qty`, `price`, `total_cost`,`seller_id`,`seller`, `sold` from `market` where `sold`='f' order by $orderby $abc limit ?,?", array($begin, $limit));
		while($market = $query_market->fetchrow()) {
			echo '<tr><br />';
			echo "<td>" . $market['item_name'] . "</td>";
			echo "<td>" . $market['price'] . "</td>";
			echo "<td>" . $market['seller'] . "</td>";
			if($market['seller']==$player->username) {
				echo "<td><a href=\"market.php?act=remove&item=" . $market['market_id'] . "\">Remove</a></td>\n";
			} else {
				echo "<td><a href=\"market_buy.php?act=buy&item=" . $market['market_id'] . "\">Buy</a></td></tr>\n";
			}
		}
		echo  '</table>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	}
	
break;

}
?>