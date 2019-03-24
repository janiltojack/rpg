<?php
/*************************************/
/*       ezRPG DynaMax script        */
/*      Written by Bogatabeav        */
/*     http://www.sunofloki.com      */
/*************************************/
require_once("lib.php");
define("PAGENAME", Forum);
$player = check_user($secret_key, $db);
require_once('lib/forum_functions.php');

// $db->debug = true; //Debug

// Keep out the Riffraff
if ($player->forum_ban > 0) {
	include_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "You are banned from the forum for " . $player->forum_ban . " more days.<br />";
	echo '<a href="home.php">Back to Home page</a>';
	include_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}

// Get Category and/or Thread info
if (isset($_GET['c_id'])) {
$cat_name = $db->execute("select * from forum_category where id=?", array($_GET['c_id']));
$catname = $cat_name->fetchrow();
} elseif (isset($_GET['th_id'])) {
$id = $_GET['th_id'];
$query = $db->execute("SELECT * FROM `forum_thread` WHERE `id`=?", array($id));
$rows = $query->fetchrow();
$cat_name = $db->execute("select * from forum_category where id=?", array($rows['cat_id']));
$catname = $cat_name->fetchrow();
}

switch ($_GET['page']) {
	
case 'cat': // View Category
include_once("templates/themes/" . $setting->theme . "/private_header.php");
//Access Test
if ($catname['access'] > $player->gm_rank) {
	echo 'You do not possess sufficient priviledges to access this page.<br />';
	echo '<a href="forum.php">Back to Forum</a>';
} else {
	echo "<a href=\"forum.php\">Main Forum</a> / " . showClean($catname['name']) . "<br />";
	// Pagination variables
	$limit = 20; // Set the amount per page
	$page_max = 10; // max amount of pagination pages shown
	$page = (intval($_GET['p']) == 0)?1:intval($_GET['p']); //Start on page 1 or $_GET['p']
	$begin = ($limit * $page) - $limit; //Starting point for query
	$total_threads = $db->getone("select count(ID) as `count` from `forum_thread` where `cat_id`=?", array($catname['id']));
	$numpages = ceil($total_threads / $limit);

	echo '<table id="forum-list-b">';
	echo '<tr>';
	echo "<td align=\"right\"><a href=\"forum.php?page=create_th&c_id=" . $catname['id'] . "\">Create New Thread</a></td>";
	echo '</tr>';
	echo '</table>';
	echo '<table id="forum-list">';
	echo '<tr>';
	echo '<th align="left">Title</th>';
	echo '<th width="10%" align="center">Replies</th>';
	echo '<th width="10%" align="center">Views</th>';
	echo '<th width="20%" align="center">Author</th>';
	echo '<th width="20%" align="center">Last Reply</th>';
	echo '</tr>';
	// Post Listing
	$result = $db->execute("SELECT * FROM `forum_thread` where cat_id =? ORDER BY sticky DESC, recent DESC LIMIT ?,?", array($catname['id'], $begin, $limit));
	while($rows = $result->fetchrow()){ // Start looping table row 
		$result2 = $db->execute("SELECT rep_id, rep_name, rep_datetime FROM `forum_reply` where th_id=? order by rep_id DESC", array($rows['id']));
		$rows2 = $result2->fetchrow();
		$newtime = date('j M y g:i a',strtotime($rows['datetime']));
		if ($rows2['rep_id'] == NULL) {
			$newtime2 = "No Replies.";
		} else {
			$newtime2 = date('j M y g:i a',strtotime($rows2['rep_datetime']));
		}
		echo '<tr>';
		if ($rows['sticky'] == 1) {
			if ($rows['locked'] == 0) {
				echo "<td align=\"left\"><a href=\"forum.php?page=th&th_id=" . $rows['id'] . "\">* " . $rows['title'] . " *</a><BR></td>";
			} else {
				echo "<td align=\"left\"><a href=\"forum.php?page=th&th_id=" . $rows['id'] . "\">* " . $rows['title'] . " (Locked) *</a><BR></td>";
			}	
		} else {
			if ($rows['locked'] == 0) {
				echo "<td align=\"left\"><a href=\"forum.php?page=th&th_id=" . $rows['id'] . "\">" . $rows['title'] . "</a><BR></td>";
			} else {
				echo "<td align=\"left\"><a href=\"forum.php?page=th&th_id=" . $rows['id'] . "\">" . $rows['title'] . " (Locked)</a><BR></td>";
			}	
		}
		echo "<td align=\"center\">" . $rows['reply'] . "</td>";
		echo "<td align=\"center\">" . $rows['view'] . "</td>";
		echo '<td align="center">';
		echo "<a href=\"profile.php?id=" . player_name($rows['name'], $db) . "\">" . player_name($rows['name'], $db) . "</a>";
		echo "<br />" . $newtime . "</td>";
		echo '<td align="center">';
		echo "<a href=\"profile.php?id=" . player_name($rows2['rep_name'], $db) . "\">" . player_name($rows2['rep_name'], $db) . "</a>";
		echo "<br />" . $newtime2 . "</td>";
		echo '</tr>';
	}
	echo '</table><br />';
		//Start of Pagination
	echo '<table width="100%">';
	echo '<tr>';
	echo '<td align="right">';
	echo '<div class="pagination">';
	//Display 'Previous' link
	echo ($page > 1 && $numpages > 1)?"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . ($page-1) . "\">&#9668;</a> ":"";
	//Display page numbers
	if ($numpages != 1) {
		if ($numpages <= $page_max){
			for ($i = 1; $i <= $numpages; $i++) {
				echo ($i == $page)?"<span class=\"current\">" . $i . "</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . $i . "\">" . $i . "</a> ";
			}
		} else {
			if ($page <= 4) {
				echo ($page == 1)?"<span class=\"current\">1</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=1\">1</a> ";
				echo ($page == 2)?"<span class=\"current\">2</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=2\">2</a> ";
				echo ($page == 3)?"<span class=\"current\">3</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=3\">3</a> ";
				echo ($page == 4)?"<span class=\"current\">4</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=4\">4</a> ";
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=5\">5</a> ";
				echo '... ';
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . $numpages . "\">" . $numpages . "</a> ";	
			} elseif ($page >= ($numpages - 3)) {
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=1\">1</a> ";
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=2\">2</a> ";
				echo '... ';
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . ($numpages - 4) . "\">" . ($numpages - 4) . "</a> ";
				echo ($page == $numpages - 3)?"<span class=\"current\">" . ($numpages - 3) . "</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . ($numpages - 3) . "\">" . ($numpages - 3) . "</a> ";
				echo ($page == $numpages - 2)?"<span class=\"current\">" . ($numpages - 2) . "</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . ($numpages - 2) . "\">" . ($numpages - 2) . "</a> ";
				echo ($page == $numpages - 1)?"<span class=\"current\">" . ($numpages - 1) . "</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
				echo ($page == $numpages)?"<span class=\"current\">" . $numpages . "</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . $numpages . "\">" . $numpages . "</a> ";
			} else {	
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=1\">1</a> ";
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=2\">2</a> ";
				echo '... ';	
				for ($i = ($page - 1); $i <= ($page + 1); $i++) {
					echo ($i == $page)?"<span class=\"current\">" . $i . "</span>" . " ":"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . $i . "\">" . $i . "</a> ";
				}				
				echo '... ';
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
				echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . $numpages . "\">" . $numpages . "</a> ";	
			}
		}
	}
	//Display the 'Next' link
	echo ($page != $numpages && $numpages > 1)?"<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "&p=" . ($page+1) . "\">&#9658;</a> ":"";
	echo '</div>';
	echo '</td></tr>';
	echo '</table>';
	// End of Pagination
}
break;

case 'create_cat':  //Create Category Form
include_once("templates/themes/" . $setting->theme . "/private_header.php");
if ($player->gm_rank >= 75) {
	// Find highest reply number. 
	$query = $db->execute("SELECT max(`id`) AS Max_id FROM `forum_category`");
	$rows = $query->fetchrow();
	// add + 1 to highest reply number and keep it in variable name "$Max_id". if there no reply yet set it = 1 
	if ($rows) {
		$Max_id = $rows['Max_id']+1;
	} else {
		$Max_id = 1;
	}
	echo '<a href="forum.php">Back to Forum</a>';
	echo '<br /><br />';
	echo '<table id="forum-list">';
	echo '<form method="post" action="forum.php?page=wcreate_cat">';
	echo "<input type=hidden name=\"max_id\" value=\"" . $Max_id . "\">";
	echo '<tr>';
	echo '<th colspan="2">Category Creation</th>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>Category Name</td>';
	echo '<td><input type="text" name="name" size="50"></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>Description</td>';
	echo '<td><textarea cols="40" rows="5" name="descript"></textarea></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>User Access</td>';
	echo '<td><select name="access">';
	echo '<option value="1">Open Access</option>';
	echo '<option value="75">Admin Only</option>';
	echo '</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>&nbsp;</td>';
	echo '<td><input type="submit" value="Enter"></td>';
	echo '</tr>';
	echo '</form>';
	echo '</table>';
} else {
	echo 'You do not possess sufficient priviledges to access this page.<br />';
}	
break;

case 'wcreate_cat':  //Create Category (write to table)
include_once("templates/themes/" . $setting->theme . "/private_header.php");
if ($player->gm_rank >= 75) {
	if(is_number($_POST['max_id'])) {
	} else {
		echo "Invalid id. <br />";
		echo '<a href="forum.php">Back to Forum</a>';
		include_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	}
	if(is_empty($_POST['name'])) {
	} else {
		echo "Please fill out category name. <br />";
		echo '<a href="forum.php">Back to Forum</a>';
		include_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	}
	if(is_empty($_POST['descript'])) {
	} else {
		echo "Please fill out category description. <br />";
		echo '<a href="forum.php">Back to Forum</a>';
		include_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	}
	if(is_number($_POST['access'])) {
	} else {
		echo "Invalid access. <br />";
		echo '<a href="forum.php">Back to Forum</a>';
		include_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	}
	$name = Clean($_POST['name']);
	$descript = Clean($_POST['descript']);
	$query = $db->execute("INSERT INTO `forum_category` (`id`, `name`, `descript`, `access`) VALUES (?, ?, ?, ?)", array($_POST['max_id'], $name, $descript, $_POST['access']));
	echo "You have successfully created the " . $_POST['name'] . " category.<br />";
	echo '<a href="forum.php">Back to Forum</a>';	
} else {
	echo 'You do not possess sufficient priviledges to access this page.<br />';
	echo '<a href="forum.php">Back to Forum</a>';
}	
break;

case 'edit_cat': //Edit Category Form
include_once("templates/themes/" . $setting->theme . "/private_header.php");
if ($player->gm_rank >= 75) {
	$query = $db->execute("SELECT * FROM `forum_category` WHERE id=?", array($_GET['c_id']));
	$row = $query->fetchrow();
	echo '<a href="forum.php">Back to Forum</a>';
	echo '<br /><br />';
	echo '<table id="forum-list">';
	echo '<form method="post" action="forum.php?page=wedit_cat">';
	echo "<input type=hidden name=\"c_id\" value=\"" . $row['id'] . "\">";
	echo '<tr>';
	echo '<th colspan="2">Edit Category</th>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>Category Name</td>';
	echo "<td><input type=\"text\" name=\"name\" size=\"50\" value=\"" . showClean($row['name']) . "\" ></td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td>Description</td>';
	echo "<td><textarea cols=\"40\" rows=\"5\" name=\"descript\" >" . showClean($row['descript']) . "</textarea></td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td>User Access</td>';
	echo '<td><select name="access">';
	echo "<option value=\"1\" " . (($row['access']=="1") ? "selected=\"selected\"":"") . ">Open Access</option>";
	echo "<option value=\"75\" " . (($row['access']=="75") ? "selected=\"selected\"":"") . ">Admin Only</option>";
	echo '</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>&nbsp;</td>';
	echo '<td><input type="submit" value="Enter"></td>';
	echo '</tr>';
	echo '</form>';
	echo '</table>';
} else {
	echo 'You do not possess sufficient priviledges to access this page.<br />';
	echo '<a href="forum.php">Back to Forum</a>';
}
break;

case 'wedit_cat':  //Edit Category (write to table)
include_once("templates/themes/" . $setting->theme . "/private_header.php");
$name = Clean($_POST['name']);
$descript = Clean($_POST['descript']);
if ($player->gm_rank >= 75) {
	$query = $db->execute("UPDATE `forum_category` SET `name`=?, `descript`=?, `access`=? WHERE `id`=?", array($name, $descript, $_POST['access'], $_POST['c_id']));
	echo "You have successfully edited the " . $_POST['name'] . " category.<br />";
	echo '<a href="forum.php">Back to Forum</a>';	
} else {
	echo 'You do not possess sufficient priviledges to access this page.<br />';
	echo '<a href="forum.php">Back to Forum</a>';
}
break;

case 'delete_cat':  // Delete category Form
include_once("templates/themes/" . $setting->theme . "/private_header.php");
if ($player->gm_rank >= 75) {
	$query = $db->execute("SELECT `id`, `name` FROM `forum_category`");
	echo '<a href="forum.php">Back to Forum</a>';
	echo '<br /><br />';
	echo '<table id="forum-list">';
	echo '<form method="post" action="forum.php?page=wdelete_cat">';
	echo '<tr>';
	echo '<th colspan="2">Choose the category to delete</th>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>Category</td>';
	echo '<td><select name="c_id">';
	while ($cats = $query->fetchrow()) {
		echo "<option value=\"" . $cats['id'] . "\">" . $cats['name'] . "</option>";
	}
	echo '</select>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>&nbsp;</td>';
	echo '<td>';
	?>
	<input type="button" onclick="if (confirm('Are you ABSOLUTELY sure you want to delete the Category?  This action will delete ALL threads within this category.')) submit();" value="Delete">
    <?php
	echo '</td>';
	echo '</tr>';
	echo '</form>';
	echo '</table>';
} else {
	echo 'You do not possess sufficient priviledges to access this page.<br />';
	echo '<a href="forum.php">Back to Forum</a>';
}
break;

case 'wdelete_cat': // Delete category (remove from table)
include_once("templates/themes/" . $setting->theme . "/private_header.php");
if ($player->gm_rank >= 75) {
	$query = $db->execute("DELETE FROM forum_category WHERE id=?", array($_POST['c_id']));
	$query = $db->execute("DELETE FROM forum_thread WHERE cat_id=?", array($_POST['c_id']));
	$query = $db->execute("DELETE FROM forum_reply WHERE cat_id=?", array($_POST['c_id']));
	echo "You have successfully deleted the category.<br />";
	echo '<a href="forum.php">Back to Forum</a>';
} else {
	echo 'You do not possess sufficient priviledges to access this page.<br />';
	echo '<a href="forum.php">Back to Forum</a>';
}
break;

case 'th':  // View Thread
// Access Test
include_once("templates/themes/" . $setting->theme . "/private_header.php");
if ($catname['access'] > $player->gm_rank) {
	echo 'You do not possess sufficient priviledges to access this page.<br />';
	echo '<a href="forum.php">Return to forum</a>';
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
// GM Nav variables
if ($rows['locked'] == 0) {
	$lock_name = "Lock ";
	$lock_link = "1";
} else {
	$lock_name = "Unlock ";
	$lock_link = "0";
}
if ($rows['sticky'] == 0) {
	$sticky_name = "Sticky ";
	$sticky_link = "1";
} else {
	$sticky_name = "Unsticky ";
	$sticky_link = "0";
} 
	echo '<table width="100%">';
	echo '<tr>';
	echo "<td><a href=\"forum.php\">Main Forum</a>  <a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "\"> / " . $catname['name'] . "</a>
 / " . $rows['title'] . "<br /></td>";
 	echo '</tr><tr>';
	echo '<td align="right">';
	//Start of Pagination
	// Pagination variables
	$limit = 15; // Set the amount per page
	$page_max = 10; // max amount of pagination pages shown
	$page = (intval($_GET['p']) == 0)?1:intval($_GET['p']); //Start on page 1 or $_GET['p']
	$begin = ($limit * $page) - $limit; //Starting point for query
	$total_threads = $db->getone("select count(rep_id) as `count` from `forum_reply` WHERE `th_id`=?", array($id));
	$numpages = ceil($total_threads / $limit);
	echo '<div class="pagination">';
	//Display 'Previous' link
	echo ($page > 1 && $numpages > 1)?"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($page-1) . "\">&#9668;</a> ":"";
	//Display page numbers
	if ($numpages != 1) {
		if ($numpages <= $page_max){
			for ($i = 1; $i <= $numpages; $i++) {
				echo ($i == $page)?"<span class=\"current\">" . $i . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $i . "\">" . $i . "</a> ";
			}
		} else {
			if ($page <= 4) {
				echo ($page == 1)?"<span class=\"current\">1</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=1\">1</a> ";
				echo ($page == 2)?"<span class=\"current\">2</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=2\">2</a> ";
				echo ($page == 3)?"<span class=\"current\">3</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=3\">3</a> ";
				echo ($page == 4)?"<span class=\"current\">4</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=4\">4</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=5\">5</a> ";
				echo '... ';
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $numpages . "\">" . $numpages . "</a> ";	
			} elseif ($page >= ($numpages - 3)) {
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=1\">1</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=2\">2</a> ";
				echo '... ';
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 4) . "\">" . ($numpages - 4) . "</a> ";
				echo ($page == $numpages - 3)?"<span class=\"current\">" . ($numpages - 3) . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 3) . "\">" . ($numpages - 3) . "</a> ";
				echo ($page == $numpages - 2)?"<span class=\"current\">" . ($numpages - 2) . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 2) . "\">" . ($numpages - 2) . "</a> ";
				echo ($page == $numpages - 1)?"<span class=\"current\">" . ($numpages - 1) . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
				echo ($page == $numpages)?"<span class=\"current\">" . $numpages . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $numpages . "\">" . $numpages . "</a> ";
			} else {	
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=1\">1</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=2\">2</a> ";
				echo '... ';	
				for ($i = ($page - 1); $i <= ($page + 1); $i++) {
					echo ($i == $page)?"<span class=\"current\">" . $i . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $i . "\">" . $i . "</a> ";
				}				
				echo '... ';
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $numpages . "\">" . $numpages . "</a> ";	
			}
		}
	}
//Display the 'Next' link
echo ($page != $numpages && $numpages > 1)?"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($page+1) . "\">&#9658;</a> ":"";
echo '</div>';
echo '</td></tr>';
echo '</table>';
// End of Pagination
// GM Editing
if ($player->gm_rank >= 75) {
	echo '<table id="forum-list-b">';
	echo '<tr>';
	echo "<td><a href=\"forum.php?page=sticky_th&th_id=" . $id . "&sticky=" . $sticky_link . "\">" . $sticky_name . " </a>/  ";
	echo "<a href=\"forum.php?page=lock_th&th_id=" . $id . "&lock=" . $lock_link . "\">" . $lock_name . " </a>/ ";
	echo "<a href=\"forum.php?page=move_th&th_id=" . $id . "\">Move </a>/ ";
	echo "<a href=\"javascript:confirmPostDelete('forum.php?page=delete_th&th_id=" . $id . "')\">Delete Entire Thread</a></td>";
	echo '</tr>';
	echo '</table>';
}
echo '<table id="forum-list">';
echo '<tr>';
echo '<th align="left" width="25%">Author</th><th align="left" width="75%">Message</th>';
echo '</tr>';
// Initial Posting
if ($page == 1) {
	$content = showClean($rows['detail']);
	$content = bbCode($content);
	$content = wordwrap($content, 80, "\n", true);
	echo '<tr>';
	echo "<td valign=\"top\" align=\"center\"><a href=\"profile.php?id=" . player_name($rows['name'], $db) . "\">" . player_name($rows['name'], $db) . "</a><br />";
	echo "<center><img src=\"./images/avatars/" . player_avatar($rows['name'], $db)  . "\"  width=\"80\" height=\"80\" /></center></td>";
	echo "<td>Posted at " . date('j M y g:i a',strtotime($rows['datetime'])) . "<br /><br />" . $content . "<br /><br />";
	if (($rows['name'] == $player->id || $player->gm_rank >= 75) && $rows['locked'] == 0) {
		echo "<a href=\"forum.php?page=edit_th&th_id=" . $id . "\">Edit</a> / ";
		echo "<a href=\"javascript:confirmPostDelete('forum.php?page=delete_th&th_id=" . $id . "')\">Delete</a>";	
	} elseif ($player->gm_rank >= 75 && $rows['locked'] == 1) {
		echo "<a href=\"forum.php?page=edit_th&th_id=" . $id . "\">Edit</a> / ";
		echo "<a href=\"javascript:confirmPostDelete('forum.php?page=delete_th&th_id=" . $id . "')\">Delete</a>";	
	}
	echo '</td>';
	echo '</tr>';
}
// Reply section of table
$result2 = $db->execute("SELECT * FROM `forum_reply` WHERE `th_id`=? ORDER BY rep_id ASC LIMIT ?,?", array($id, $begin, $limit));
while($rows2 = $result2->fetchrow()){
	$reply_content = showClean($rows2['rep_detail']);
	$reply_content = bbCode($reply_content);
	$reply_content = wordwrap($reply_content, 75, "\n", true);
	echo '<tr>';
	echo "<td valign=\"top\" align=\"center\"><a href=\"profile.php?id=" . player_name($rows2['rep_name'], $db) . "\">" . player_name($rows2['rep_name'], $db) . "</a><br />";
	echo "<center><img src=\"./images/avatars/" . player_avatar($rows2['rep_name'], $db)  . "\"  width=\"80\" height=\"80\" /></center></td>";
	echo "<td>";
	echo "Posted at " . date('j M y g:i a',strtotime($rows2['rep_datetime'])) . "<br /><br />" . $reply_content . "<br /><br />";
	if (($player->gm_rank >= 75 || $rows2['rep_name'] == $player->id) && $rows['locked'] == 0) {
		echo "<a href=\"forum.php?page=edit_rep&th_id=" . $id . "&id=" . $rows2['rep_id'] . "\">Edit</a> / ";
		echo "<a href=\"javascript:confirmPostDelete('forum.php?page=delete_rep&th_id=" . $id . "&id=" . $rows2['rep_id'] . "')\">Delete</a>";
	} elseif ($player->gm_rank >= 75 && $rows['locked'] == 1) {
		echo "<a href=\"forum.php?page=edit_rep&th_id=" . $id . "&id=" . $rows2['rep_id'] . "\">Edit</a> / ";
		echo "<a href=\"javascript:confirmPostDelete('forum.php?page=delete_rep&th_id=" . $id . "&id=" . $rows2['rep_id'] . "')\">Delete</a>";
	}
	echo '</td>';
	echo '</tr>';
}
echo "</table><br>";
	//Start of Pagination
	echo '<table width="100%">';
	echo '<tr>';
	echo '<td align="right">';
	// Pagination variables
	echo '<div class="pagination">';
	//Display 'Previous' link
	echo ($page > 1 && $numpages > 1)?"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($page-1) . "\">&#9668;</a> ":"";
	//Display page numbers
	if ($numpages != 1) {
		if ($numpages <= $page_max){
			for ($i = 1; $i <= $numpages; $i++) {
				echo ($i == $page)?"<span class=\"current\">" . $i . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $i . "\">" . $i . "</a> ";
			}
		} else {
			if ($page <= 4) {
				echo ($page == 1)?"<span class=\"current\">1</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=1\">1</a> ";
				echo ($page == 2)?"<span class=\"current\">2</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=2\">2</a> ";
				echo ($page == 3)?"<span class=\"current\">3</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=3\">3</a> ";
				echo ($page == 4)?"<span class=\"current\">4</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=4\">4</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=5\">5</a> ";
				echo '... ';
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $numpages . "\">" . $numpages . "</a> ";	
			} elseif ($page >= ($numpages - 3)) {
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=1\">1</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=2\">2</a> ";
				echo '... ';
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 4) . "\">" . ($numpages - 4) . "</a> ";
				echo ($page == $numpages - 3)?"<span class=\"current\">" . ($numpages - 3) . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 3) . "\">" . ($numpages - 3) . "</a> ";
				echo ($page == $numpages - 2)?"<span class=\"current\">" . ($numpages - 2) . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 2) . "\">" . ($numpages - 2) . "</a> ";
				echo ($page == $numpages - 1)?"<span class=\"current\">" . ($numpages - 1) . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
				echo ($page == $numpages)?"<span class=\"current\">" . $numpages . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $numpages . "\">" . $numpages . "</a> ";
			} else {	
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=1\">1</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=2\">2</a> ";
				echo '... ';	
				for ($i = ($page - 1); $i <= ($page + 1); $i++) {
					echo ($i == $page)?"<span class=\"current\">" . $i . "</span>" . " ":"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $i . "\">" . $i . "</a> ";
				}				
				echo '... ';
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
				echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $numpages . "\">" . $numpages . "</a> ";	
			}
		}
	}
	//Display the 'Next' link
	echo ($page != $numpages && $numpages > 1)?"<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . ($page+1) . "\">&#9658;</a> ":"";
	echo '</div>';
	echo '</td></tr>';
	echo '</table>';
	// End of Pagination
$result3 = $db->execute("SELECT `view` FROM `forum_thread` WHERE `id`=?", array($id));
$rows3 = $result3->fetchrow();
$view = $rows3['view'];
// if have no counter value set counter = 1
if(empty($view)) {
$view = 1;
$result4 = $db->execute("INSERT INTO `forum_thread` (`view`) VALUES (?) WHERE `id`=?", array($view, $id));
}
// count more value
$addview = $view+1;
$result5 = $db->execute("update `forum_thread` set `view`=? WHERE `id`=?", array($addview, $id));
// Comment Form
if ($rows['locked'] == 0) {
echo '<table id="forum-list">';
echo '<form name="form1" method="post" action="forum.php?page=wcreate_rep">';
echo "<input name=\"name\" type=\"hidden\" value=\"" . $player->id . "\">";
echo "<input name=\"id\" type=\"hidden\" value=\"" . $id . "\">";
echo "<input name=\"p\" type=\"hidden\" value=\"" . $_GET['p'] . "\">";
echo "<input name=\"cat_id\" type=\"hidden\" value=\"" . $rows['id'] . "\">";
echo '<tr>';
echo '<th align="left"><b>Reply</b></th>';
echo '</tr>';
echo '<tr>';
echo '<td style="{border-bottom:0px;}"><br />Comment<br />';
echo '<textarea name="rep_detail" cols="51" rows="8" id="rep_detail"></textarea></td>';
echo '</tr>';
echo '<tr>';
echo '<td style="{border-top:0px;}">';
echo '<input type="submit" name="submit" value="Submit"> <input type="reset" name="Submit2" value="Reset"></td>';
echo '</tr>';
echo '</form>';
echo '</table>';
} else {
	echo "This thread has been locked.";
}
break;

case 'create_th':  // Create Thread Form
require_once("templates/themes/" . $setting->theme . "/private_header.php");
if(is_number($catname['id'])) {
} else {
	echo "Not a valid category. <br />";
	echo '<a href="forum.php">Back to Forum</a>';
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
echo "<a href=\"forum.php?page=cat&c_id=" . $catname['id'] . "\">Back to Category</a>";
echo '<br /><br />';
echo '<table id="forum-list">';
echo '<form id="form1" name="form1" method="post" action="forum.php?page=wcreate_th">';
echo "<input name=\"name\" type=\"hidden\" value=\"" . $player->id . "\">";
echo "<input name=\"cat_id\" type=\"hidden\" value=\"" . $catname['id'] . "\">";
echo '<tr>';
echo '<th align="left" colspan="3">Create New Thread</th>';
echo '</tr>';
echo '<tr>';
echo '<td style="{border-bottom:0px;}">';
echo 'Title<br />';
echo '<input name="title" type="text" id="title" size="60" /><br />';
echo 'Comment<br />';
echo '<textarea name="detail" cols="51" rows="8" id="detail"></textarea><br />';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td style="{border-top:0px;}"><input type="submit" name="Submit" value="Submit" /> <input type="reset" name="Submit2" value="Reset" /></td>';
echo '</tr>';
echo '</form>';
echo '</table>';
break;

case 'wcreate_th':  // Create Thread (write to table)
if ((strlen($_POST['title'])) < 3 ||  (strlen($_POST['title']) > 50)) {
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "Not a valid title. <br />";
	echo "<a href=forum.php?page=cat&c_id=" . $_POST['cat_id'] . ">Back to Category</a>";
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
if((strlen($_POST['detail'])) < 3) {
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "Not a valid post. <br />";
	echo "<a href=forum.php?page=cat&c_id=" . $_POST['cat_id'] . ">Back to Category</a>";
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
$title=Clean($_POST['title']);
$detail=Clean($_POST['detail']);
$detail = nl2br($detail);
$name=$_POST['name'];
$cat_id=$_POST['cat_id'];
$datetime = date("Y-m-d H:i:s"); // create date and time 
$result = $db->execute("INSERT INTO `forum_thread`(title, detail, name, datetime, recent, cat_id) VALUES ('$title', '$detail', '$name', '$datetime', '$datetime','$cat_id')");
header("Location: forum.php?page=cat&c_id=" . $cat_id . "");
exit;
break;

case 'edit_th':  // Edit Thread Form
require_once("templates/themes/" . $setting->theme . "/private_header.php");
if ($player->gm_rank >= 75 || $rows['name'] == $player->id) {
	$query = $db->execute("SELECT * FROM `forum_thread` WHERE `id`=?", array($rows['id']));
	$thread = $query->fetchrow();
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "<a href=\"forum.php?page=th&th_id=" . $thread['id'] . "\">Back to Thread</a>";
	echo '<br /><br />';
	echo '<table id="forum-list">';
	echo "<form method=\"post\" action=\"forum.php?page=wedit_th\">";
	echo "<input type=hidden name=\"id\" value=\"" . $thread['id'] . "\">";
	echo '<tr>';
	echo '<th colspan="2">Edit Thread</th>';
	echo '</tr>';
	echo '<tr>';
	echo '<td width="20%">Title</td>';
	echo "<td>" . $thread['title'] . "</td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td>Content</td>';
	echo "<td><textarea name=\"detail\" cols=\"45\" rows=\"8\">" . showClean($thread['detail']) . "</textarea></td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td> </td>';
	echo '<td><input type="submit" value="Edit"></td>';
	echo '</tr>';
	echo '</form>';
	echo '</table>';
}
	break;

case 'wedit_th':  // Edit Thread (write to table)
if ($player->gm_rank >= 75 || $rows['name'] == $player->id) {
	if(is_number($_POST['id'])) {
	} else {
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo "Thread id cannot be altered, manually. <br />";
		echo '<a href="forum.php">Back to Forum</a>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	}
	if((strlen($_POST['detail'])) < 3) {
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo "Invalid post. <br />";
		echo '<a href="forum.php">Back to Forum</a>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	}
	$query = $db->execute("UPDATE `forum_thread` SET `detail`=? WHERE `id`=?", array(($_POST['detail']), $_POST['id']));
	header("Location: forum.php?page=th&th_id=" . $_POST['id'] . "");
	exit;
}
break;

case 'move_th':  // Move Thread Form
if ($player->gm_rank >= 75) {	
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "<a href=\"forum.php?page=th&th_id=" . $rows['id'] . "\">Back to Thread</a>";
	echo '<br /><br />';
	echo '<table id="forum-list">';
	echo "<form method=\"post\" action=\"forum.php?page=wmove_th\">";
	echo "<input type=hidden name=\"id\" value=\"" . $rows['id'] . "\">";
	echo '<tr>';
	echo '<th colspan="2">Move Thread</th>';
	echo '</tr>';
	echo '<tr>';
	echo '<td width="40%">Title</td>';
	echo "<td>" . $rows['title'] . "</td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td>Content</td>';
	echo "<td><textarea name=\"detail\" cols=\"35\" rows=\"8\" readonly>" . $rows['detail'] . "</textarea></td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td>Current Category</td>';
	echo "<td>" . $catname['name'] . "</td>";
	echo '</tr>';	
	echo '<tr>';
	echo '<td>Move to</td>';
	echo '<td><select name="new_cat">';
	$query2 = $db->execute("SELECT * FROM `forum_category`");
	while ($cats = $query2->fetchrow()) {
	echo "<option value=\"" . $cats['id'] . "\" >" . $cats['name'] . "</option>";
	}
	echo '</select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td> </td>';
	echo '<td><input type="submit" value="Move"></td>';
	echo '</tr>';	
	echo '</form>';
	echo '</table>';	
}
break;

case 'wmove_th': // Move Thread (write to table)
if ($player->gm_rank >= 75) {
	if(is_number($_POST['new_cat'])) {
	} else {
		require_once("templates/themes/" . $setting->theme . "/private_header.php");
		echo "Invalid category. <br />";
		echo '<a href="forum.php">Back to Forum</a>';
		require_once("templates/themes/" . $setting->theme . "/private_footer.php");
		exit;
	}
	$query = $db->execute("UPDATE `forum_thread` SET `cat_id`=? WHERE `id`=?", array(($_POST['new_cat']), $_POST['id']));
	$query2 = $db->execute("UPDATE `forum_reply` SET `cat_id`=? WHERE `th_id`=?", array(($_POST['new_cat']), $_POST['id']));
	header("Location: forum.php?page=th&th_id=" . $_POST['id'] . "");
}
	break;

case 'sticky_th': // Stick Thread to top of list
if ($player->gm_rank >= 75) {
	$query = $db->execute("UPDATE `forum_thread` SET sticky=? WHERE id=?", array($_GET['sticky'], $id));
	header("Location: forum.php?page=th&th_id=" . $id . "");
	exit;
}
	break;

case 'lock_th': // Close thread to new replies
if ($player->gm_rank >= 75) {
	$query = $db->execute("UPDATE `forum_thread` SET locked=? WHERE id=?", array($_GET['lock'], $id));
	header("Location: forum.php?page=th&th_id=" . $id . "");
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
break;

case 'delete_th':  // Delete Thread
if ($player->gm_rank >= 75 || $rows['name'] == $player->id) {
	$query = $db->execute("DELETE FROM `forum_thread` WHERE id=?", array($id));
	$query2 = $db->execute("DELETE FROM `forum_reply` WHERE th_id=?", array($id));
	header("Location: forum.php?page=cat&c_id=" . $rows['cat_id']. "");
	exit;
}
break;

case 'wcreate_rep':  // Create Reply (write to table)
$id = $_POST['id'];
$rep_detail = Clean($_POST['rep_detail']); 
$p = $_POST['p'];

if(strlen($rep_detail) < 3) {
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "You cannot post that reply. <br />";
	echo "<a href=\"forum.php?page=th&th_id=" . $id . "&p=" . $p ."\">Back to thread</a>";
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
// check if thread is locked (stops ppl from using $_GET to hack into your locked threads)
$result = $db->execute("select locked, cat_id from forum_thread where id=$id");
$lock = $result->fetchrow();
$lock_test = $lock['locked'];
$cat_id = $lock['cat_id'];
if ($lock_test == 1) {
header("Location: forum.php?page=th&th_id=" . $id . "");
}
// Find highest reply number. 
$result = $db->execute("SELECT max(`rep_id`) AS Maxrep_id FROM `forum_reply` WHERE `th_id`=?", array($id));
$rows = $result->fetchrow();
// add + 1 to highest reply number and keep it in variable name "$Max_id". if there no reply yet set it = 1 
if ($rows) {
	$Max_id = $rows['Maxrep_id']+1;
} else {
	$Max_id = 1;
}


$datetime = date("Y-m-d H:i:s"); // create date and time 
// Insert reply 
$result2 = $db->execute("INSERT INTO `forum_reply` (`th_id`, `rep_id`, `rep_name`, `rep_detail`, `rep_datetime`, `cat_id`) VALUES ('$id', '$Max_id', '$player->id', '$rep_detail', '$datetime', '$cat_id')");
if($result2){
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "Successful<BR>";
	echo "<a href='forum.php?page=th&th_id=".$id."'>View your reply</a>";
	// If added new reply, add value +1 in reply column 
	$datetime = date("Y-m-d H:i:s"); // create date and time 
	$result3 = $db->execute("UPDATE `forum_thread` SET `reply`=?, `recent`=? WHERE `id`=?", array($Max_id, $datetime, $id));
}
break;

case 'edit_rep':  // Edit Reply Form
$query = $db->execute("SELECT * FROM `forum_reply` WHERE `th_id`=? AND `rep_id`=? ", array($_GET['th_id'], $_GET['id']));
$reply = $query->fetchrow();
if ($player->gm_rank >= 75 || $reply['rep_name'] == $player->id) {
	$query2 = $db->execute("SELECT * FROM `forum_thread` WHERE `id`=?", array($_GET['th_id']));
	$reply2 = $query2->fetchrow();
	$rep_detail = showClean($reply['rep_detail']);
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "<a href=\"forum.php?page=th&th_id=" . $_GET['th_id'] . "\">Back to Thread</a>";
	echo '<br /><br />';
	echo '<table id="forum-list">';
	echo "<form method=\"post\" action=\"forum.php?page=wedit_rep\">";
	echo "<input type=hidden name=\"th_id\" value=\"" . $reply2['id'] . "\">";
	echo "<input type=hidden name=\"id\" value=\"" . $reply['rep_id'] . "\">";
	echo '<tr>';
	echo '<th colspan="2">Edit Reply</th>';
	echo '</tr>';
	echo '<tr>';
	echo '<td width="20%">Title</td>';
	echo "<td>" . $reply2['title'] . "</td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td>Content</td>';
	echo "<td><textarea name=\"reply\" cols=\"45\" rows=\"8\">" . $rep_detail . "</textarea></td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td> </td>';
	echo '<td><input type="submit" value="Edit"></td>';
	echo '</tr>';
	echo '</form>';
	echo '</table>';
} else {
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "You cannot edit this reply.<br /> <a href=\"forum.php?page=th&th_id=" . $_GET['th_id'] . "\">Back to Thread</a>";
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
break;

case 'wedit_rep':  // Edit Reply (write to table)
$comment = clean($_POST['reply']);

if(strlen($comment) < 3) {
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "Invalid reply. <br />";
	echo '<a href="forum.php">Back to Forum</a>';
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
$query = $db->execute("SELECT * FROM `forum_reply` WHERE `th_id`=? AND `rep_id`=?", array($_POST['th_id'], $_POST['id']));
$reply = $query->fetchrow();
if ($player->gm_rank >= 75 || $reply['rep_name'] == $player->id) {
	$query = $db->execute("UPDATE `forum_reply` SET `rep_detail`=? WHERE `th_id`=? AND `rep_id`=?", array($comment, $reply['th_id'], $reply['rep_id']));
	header("Location: forum.php?page=th&th_id=" . $reply['th_id'] . "");
	exit;
} else {
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "You cannot edit this reply.<br /> <a href=\"forum.php?page=th&th_id=" . $reply['th_id'] . "\">Back to Thread</a>";
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
break;

case 'delete_rep':  // Delete Reply
$query = $db->execute("SELECT * FROM `forum_reply` WHERE `th_id`=? AND `rep_id`=? ", array($_GET['th_id'], $_GET['id']));
$reply = $query->fetchrow();
if ($player->gm_rank >= 75 || $reply['rep_name'] == $player->id) {	
	$query = $db->execute("UPDATE `forum_thread` SET `reply`=? WHERE `id`=?", array(($rows['reply']-1), $reply['th_id']));
	$query2 = $db->execute("DELETE FROM `forum_reply` WHERE th_id=? AND `rep_id`=?", array($reply['th_id'], $reply['rep_id']));
	header("Location: forum.php?page=th&th_id=" . $reply['th_id']. "");
	exit;
} else {
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "You cannot delete this reply.<br /> <a href=\"forum.php?page=th&th_id=" . $reply['th_id']. "\">Back to Thread</a>";
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
break;

default: //Forum View
include_once("templates/themes/" . $setting->theme . "/private_header.php");
// GM Navigation
if ($player->gm_rank >= 75) {
	echo '<table id="forum-list-b">';
	echo '<tr>';
	echo '<td><a href="forum.php?page=create_cat">Create New Category</a> / ';
	echo '<a href="forum.php?page=delete_cat">Delete Category</a></td>';
	echo '</tr>';
	echo '</table>';
}
// Forum Table start
echo '<table id="forum-list">';
echo '<tr>';
echo '<th width="50%" align="left">Forum</th>';
echo '<th width="10%" align="center">Threads</th>';
echo '<th width="25%" align="center">Most Recent Thread</th>';
echo '</tr>';
// User Threads
$query = $db->execute("SELECT * FROM `forum_category` WHERE `access`=1 ORDER BY id");
while($rows = $query->fetchrow()){ // Start looping table row
	$thread_count = $db->getone("SELECT count(id) as `count` FROM forum_thread WHERE cat_id=?", array($rows['id']));
	$last_thread = $db->execute("SELECT title, name, datetime FROM forum_thread WHERE cat_id=? ORDER BY datetime desc", array($rows['id']));
	$recent_thread = $last_thread->fetchrow();
	echo '<tr>';
	echo "<td align=\"left\"><b><a href=\"forum.php?page=cat&c_id=" . $rows['id'] . "\">" . showClean($rows['name']) . "</a>";
	echo "</b><br />" . showClean($rows['descript']);
	if ($player->gm_rank >= 75) {
		echo "<br /><a href=\"forum.php?page=edit_cat&c_id=" . $rows['id'] . "\">Edit Category</a>";
	}
	echo '</td>';
	echo "<td align=\"center\">" . $thread_count . "</td>";
	if ($recent_thread['title'] == NULL) {
		echo "<td align=\"center\">No Threads<br />";
	} else {
		echo "<td align=\"center\">" . $recent_thread['title'] . "<br />";
		echo "by <a href=\"profile.php?id=" . player_name($recent_thread['name'], $db) . "\">" . player_name($recent_thread['name'], $db) . "</a><br />";
		echo date('j M y g:i a',strtotime($recent_thread['datetime']));
	}
	echo '</td></tr>';
}
// Admin Threads
if ($player->gm_rank >= 75) {
	$query = $db->execute("SELECT * FROM `forum_category` WHERE `access`>=75 ORDER BY id");
	while($rows = $query->fetchrow()){ // Start looping table row
		$thread_count = $db->getone("SELECT count(id) as `count` FROM forum_thread WHERE cat_id=?", array($rows['id']));
		$last_thread = $db->execute("SELECT title, name, datetime FROM forum_thread WHERE cat_id=? ORDER BY datetime desc", array($rows['id']));
		$recent_thread = $last_thread->fetchrow();
		echo '<tr>';
		echo "<td align=\"left\"><b><a href=\"forum.php?page=cat&c_id=" . $rows['id'] . "\">" . showClean($rows['name']) . "</a>";
		echo "</b><br />" . showClean($rows['descript']);
		if ($player->gm_rank >= 75) {
			echo "<br /><a href=\"forum.php?page=edit_cat&c_id=" . $rows['id'] . "\">Edit Category</a>";
		}
		echo "</td>";		
		echo "<td align=\"center\">" . $thread_count . "</td>";
		if ($recent_thread['title'] == NULL) {
			echo "<td align=\"center\">No Threads<br />";
		} else {
			echo "<td align=\"center\">" . $recent_thread['title'] . "<br />";
		echo "by <a href=\"profile.php?id=" . player_name($recent_thread['name'], $db) . "\">" . player_name($recent_thread['name'], $db) . "</a><br />";
			echo date('j M y g:i a',strtotime($recent_thread['datetime']));
		}
		echo '</td></tr>';
	}
}
echo '</table>';
break;
}

require_once("templates/themes/" . $setting->theme . "/private_footer.php");
?>