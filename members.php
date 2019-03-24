<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_members']);
$player = check_user($secret_key, $db);

require_once("templates/themes/" . $setting->theme . "/private_header.php");

//Start of Pagination
// Pagination variables
$limit = 30; // Set the amount per page
$page_max = 10; // max amount of pagination pages shown
$page = (intval($_GET['p']) == 0)?1:intval($_GET['p']); //Start on page 1 or $_GET['page']
$begin = ($limit * $page) - $limit; //Starting point for query
$total_players = $db->getone("select count(ID) as `count` from `players`");
$numpages = ceil($total_players / $limit);
echo '<div class="pagination">';
//Display 'Previous' link
echo ($page > 1 && $numpages > 1)?"<a href=\"members.php?p=" . ($page-1) . "\">&#9668;</a> ":"";
//Display page numbers
if ($numpages != 1) {
	if ($numpages <= $page_max){
		for ($i = 1; $i <= $numpages; $i++) {
			echo ($i == $page)?"<span class=\"current\">" . $i . "</span>" . " ":"<a href=\"members.php?p=" . $i . "\">" . $i . "</a> ";
		}
	} else {
		if ($page <= 4) {
			echo ($page == 1)?'<span class="current">1</span>' . " ":'<a href="members.php?p=1">1</a> ';
			echo ($page == 2)?'<span class="current">2</span>' . " ":'<a href="members.php?p=2">2</a> ';
			echo ($page == 3)?'<span class="current">3</span>' . " ":'<a href="members.php?p=3">3</a> ';
			echo ($page == 4)?'<span class="current">4</span>' . " ":'<a href="members.php?p=4">4</a> ';
			echo '<a href="members.php?p=5">5</a> ';
			echo '... ';
			echo "<a href=\"members.php?p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
			echo "<a href=\"members.php?p=" . $numpages . "\">" . $numpages . "</a> ";	
		} elseif ($page >= ($numpages - 3)) {
			echo '<a href="members.php?p=1">1</a> ';
			echo '<a href="members.php?p=2">2</a> ';
			echo '... ';
			echo "<a href=\"members.php?p=" . ($numpages - 4) . "\">" . ($numpages - 4) . "</a> ";
			echo ($page == $numpages - 3)?"<span class=\"current\">" . ($numpages - 3) . "</span>" . " ":"<a href=\"members.php?p=" . ($numpages - 3) . "\">" . ($numpages - 3) . "</a> ";
			echo ($page == $numpages - 2)?"<span class=\"current\">" . ($numpages - 2) . "</span>" . " ":"<a href=\"members.php?p=" . ($numpages - 2) . "\">" . ($numpages - 2) . "</a> ";
			echo ($page == $numpages - 1)?"<span class=\"current\">" . ($numpages - 1) . "</span>" . " ":"<a href=\"members.php?p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
			echo ($page == $numpages)?"<span class=\"current\">" . $numpages . "</span>" . " ":"<a href=\"members.php?p=" . $numpages . "\">" . $numpages . "</a> ";
		} else {	
			echo '<a href="members.php?p=1">1</a> ';
			echo '<a href="members.php?p=2">2</a> ';
			echo '... ';
			for ($i = ($page - 1); $i <= ($page + 1); $i++) {
				echo ($i == $page)?"<span class=\"current\">" . $i . "</span>" . " ":"<a href=\"members.php?p=" . $i . "\">" . $i . "</a> ";
			}		
			echo '... ';
			echo "<a href=\"members.php?p=" . ($numpages - 1) . "\">" . ($numpages - 1) . "</a> ";
			echo "<a href=\"members.php?p=" . $numpages . "\">" . $numpages . "</a> ";	
		}
	}
}
//Display the 'Next' link
echo ($page != $numpages && $numpages > 1)?"<a href=\"members.php?p=" . ($page+1) . "\">&#9658;</a> ":"";
// End of Pagination
?>
</div>
<?="<table id=\"forum-list-b\"><tr><td><b>" . $lang['keyword_total'] . " " . $lang['keyword_members'] . "</b>: " . $total_players . "</td></tr></table>"?>
<br />
<table id="forum-list">
<tr>
<th align="left" width="60%"><b><?=$lang['keyword_username']?></b></td>
<th align="left" width="20%"><b><?=$lang['keyword_level']?></b></td>
<th align="left" width="20%"><b><?=$lang['keyword_actions']?></b></td>
</tr>
<?php
//Select all members ordered by level (highest first, members table also doubles as rankings table)
$query = $db->execute("select `id`, `username`, `level` from `players` order by `level` desc limit ?,?", array($begin, $limit));
while($member = $query->fetchrow()) {
	echo "<tr>\n";
	echo "<td><a href=\"profile.php?id=" . $member['username'] . "\">";
	echo ($member['username'] == $player->username)?"<b>":"";
	echo $member['username'];
	echo ($member['username'] == $player->username)?"</b>":"";
	echo "</a></td>\n";
	echo "<td>" . $member['level'] . "</td>\n";
	echo "<td><a href=\"mail.php?act=compose&to=" . $member['username'] . "\">" . $lang['keyword_mail'] . "</a> | <a href=\"battle.php?act=attack&username=" . $member['username'] . "\">" . $lang['keyword_attack'] . "</a></td>\n";
	echo "</tr>\n";
}
?>
</table>

<?php

require_once("templates/themes/" . $setting->theme . "/private_footer.php");
?>