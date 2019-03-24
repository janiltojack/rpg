<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
require_once("lib.php");
define("PAGENAME", $lang['page_home']);
$player = check_user($secret_key, $db);
require_once("templates/themes/" . $setting->theme . "/private_header.php");
require_once("lib/forum_functions.php");
?>
<script>edToolbar('detail'); </script>
<?php
echo '<textarea class="ed" name="detail" cols="51" rows="8" id="detail"></textarea><br />';
$in = "[b]test's it    here.[/b]\n</b>";
$newin =  Clean($in);
$unclean = ShowClean($newin) . "<br />";
$br= nl2br($unclean);
echo "Unsanitized: " .$in . "<br />";
echo "Sanitized: " . $newin . "<br />";
echo "BBCoded:" . bbCode($br) . "<br />";
echo Convert($in) . "<br />";
echo nl2br($newin);
if(is_text($in)) {
} else {
	require_once("templates/themes/" . $setting->theme . "/private_header.php");
	echo "Not a valid comment. <br />";
	echo '<a href="forum.php">Back to Forum</a>';
	require_once("templates/themes/" . $setting->theme . "/private_footer.php");
	exit;
}
phpinfo();
require_once("templates/themes/" . $setting->theme . "/private_footer.php");
?>