<?php
/*************************************/
/*           ezRPG script            */
/*         Written by Zeggy          */
/*    http://www.ezrpgproject.com    */
/*************************************/
session_start();
require_once('config.php');
require_once('lib/functions.php');
require_once('lib/adodb/adodb.inc.php'); //Include adodb files
$db = &ADONewConnection('mysql'); //Connect to database
$db->Connect($config_server, $config_username, $config_password, $config_database); //Select table
$db->SetFetchMode(ADODB_FETCH_ASSOC); //Fetch associative arrays
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC; //Fetch associative arrays
//$db->debug = true; //Debug

//Include language file
$language_include = "lib/languages/" . $config_language . ".php"; //Location of language files
if (file_exists($language_include)) {
	require_once($language_include); //Include language file specified in the config file
} else {
	require_once("lib/languages/en.php"); //Include default language file
}
//Get all game settings
$query = $db->execute("select `name`, `value` from `settings`");
while ($set = $query->fetchrow()) {
	$setting->$set['name'] = $set['value'];
}
//Get the player's IP address
$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
//Get applicable layout
$query = $db->execute("select * from `layouts` where `name`=?", array($setting->layout));
$layout = $query->fetchrow();
//Get applicable layout pages
$current_wrap = "templates/layouts/" . $setting->layout . "-wrap.php";
$current_sid1 = "templates/layouts/" . $setting->layout . "-sid1.php";
$current_sid2 = "templates/layouts/" . $setting->layout . "-sid2.php";
$current_close = "templates/layouts/" . $setting->layout . "-close.php";
?>