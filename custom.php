<?php

require_once("lib.php");
define("PAGENAME", "Player Customization");
$player = check_user($secret_key, $db);

//from settings table


switch($_GET['custom'])
{
	case "avatar":
		
		$player = check_user($secret_key, $db);
		
		include_once("templates/themes/" . $setting->theme . "/private_header.php");
		
		echo "<fieldset>";

		echo "<legend><b>Player Customization</b></legend>";

		echo "<center><br /><input type='button' VALUE='Change Avatar' ONCLICK=\"window.location.href='custom.php?custom=avatar'\">";
		
		echo "<input type='button' VALUE='Change Name' ONCLICK=\"window.location.href='custom.php?custom=namechange'\">";
		
		echo "<input type='button' VALUE='Change Password' ONCLICK=\"window.location.href='custom.php?custom=passchange'\"><br /><br />";
		
		echo "<input type='button' VALUE='Change Email' ONCLICK=\"window.location.href='custom.php?custom=emailchange'\">";
		
		echo "<input type='button' VALUE='Change Signature' ONCLICK=\"window.location.href='custom.php?custom=sigchange'\">";

		echo "<input type='button' VALUE='Change Gender' ONCLICK=\"window.location.href='custom.php?custom=genderchange'\"></center>";
		
		echo "<br>";

		echo "</fieldset><br /><br />";	
		echo "<fieldset>";
		
		echo "<legend>Avatar Upload</legend>";

$error = 0;

		//create avatars folder and place this file in it
function getExtension($str) {
	
		$i = strrpos($str,".");
		if (!$i) { return ""; }
		$l = strlen($str) - $i;
		$ext = substr($str,$i+1,$l);
		return $ext;
		}
		
function do_upload($upload_dir, $upload_url,$user) {

		$temp_name = $_FILES['userfile']['tmp_name'];
		$file_name = $_FILES['userfile']['name']; 
		$extension = getExtension($file_name);
		$extension2 = strtolower($extension);
		$newfile_name = $user.'.'.$extension2;
		$file_type = $_FILES['userfile']['type']; 
		$file_size = $_FILES['userfile']['size']; 
		$result    = $_FILES['userfile']['error'];
		$file_url  = $upload_url.$newfile_name;
		$file_path = $upload_dir.$newfile_name;
		

		//File Name Check
    	if ( $file_name =="") { 
    		$message = "Invalid File Name Specified";
    		return $message;
			
    	}
		
		//File Type Check
		if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif"))
		{
		echo '<h1>Unknown extension!</h1>';
		$errors=1;
		}
		
    	//File Size Check
    	else if ( $file_size > $maxavatar) {
        	$message = "The file size is over ".number_format($maxavatar)." bits.";
        	return $message;
    	}


    		$result  =  move_uploaded_file($temp_name, $file_path);

    		$message = ($result)?"Avatar changed." :
    	      "Something is wrong with uploading a file.";

    		return $message;


		}

		
		
		$site_name = $_SERVER['HTTP_HOST'];
		$url_dir = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		$url_this =  "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		$upload_dir = "images/avatars/";
		$upload_url = $url_dir."images/avatars/";
		$message ="";
		$extension = getExtension($file_name);
		$extension = strtolower($extension);


		if ($_FILES['userfile']) {
			$message = do_upload($upload_dir, $upload_url,$player->id);
			$query = $db->execute("update `players` set `image`=? where `id`=?", array($player->id.'.'.getExtension($_FILES['userfile']['name']), $player->id));

		}
		else {
			$message = "<br><center>You may only upload jpg, gif or png files with a ".number_format($maxavatar)." bit Maximum.</center><br>";
		}

		print $message;


		?>   

			<form name="upload" id="upload" method="post" ENCTYPE="multipart/form-data" >
            <p>
  			<p><label for='browse'>Upload Image</label><input type="file" style="width: 300px;" id="userfile" name="userfile">
  			<p class='submit'><label for='kludge'></label><input type="submit" name="upload" value="Upload">
			</form> </p>
            </fieldset>
            
<?php

		include_once("templates/themes/" . $setting->theme . "/private_footer.php");

		break;
		
	
	
	
case "namechange":
		
		$player = check_user($secret_key, $db);
		
		include_once("templates/themes/" . $setting->theme . "/private_header.php");
		
		echo "<fieldset>";

		echo "<legend><b>Player Customization</b></legend>";

		echo "<center><br /><input type='button' VALUE='Change Avatar' ONCLICK=\"window.location.href='custom.php?custom=avatar'\">";
		
		echo "<input type='button' VALUE='Change Name' ONCLICK=\"window.location.href='custom.php?custom=namechange'\">";
		
		echo "<input type='button' VALUE='Change Password' ONCLICK=\"window.location.href='custom.php?custom=passchange'\"><br /><br />";
		
		echo "<input type='button' VALUE='Change Email' ONCLICK=\"window.location.href='custom.php?custom=emailchange'\">";
		
		echo "<input type='button' VALUE='Change Signature' ONCLICK=\"window.location.href='custom.php?custom=sigchange'\">";

		echo "<input type='button' VALUE='Change Gender' ONCLICK=\"window.location.href='custom.php?custom=genderchange'\"></center>";
		
		echo "<br>";

		echo "</fieldset><br /><br />";
		
		echo "<fieldset>";
		
		echo "<legend>Name Change</legend>";


$msg1 = "<font color=\"red\">"; //Username error?

$error = 0;

	//Check if username has already been used

	

if ($_POST['newname'])

{
	$query = $db->execute("select username from `players` where `username`=?", array($_POST['newname']));

	
	//Check username

	if (!$_POST['newname']) 
	
	{ 
		//If username isn't filled in...

		$msg1 .= "You need to fill in your new username!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}

	else if (strlen($_POST['newname']) < $minname)

	{ //If username is too short...

		$msg1 .= "Your username must be longer than ".$minname." characters!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}
	else if (strlen($_POST['newname']) > $maxname)

	{ //If username is too short...

		$msg1 .= "Your username must be shorter than ".$maxname." characters!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}

	else if (!preg_match("/^[-_a-zA-Z0-9]+$/", $_POST['newname']))

	{ //If username contains illegal characters...

		$msg1 .= "Your username may contain only alphanumerical characters!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}

	else if ($query->recordcount() > 0)

	{

		$msg1 .= "That username has already been used.<br />\n";

		$error = 1; //Set error check

	}

	

	if ($error == 0)

	{

	$query = $db->execute("update `players` set `username`=? where id=?", array($_POST['newname'], $player->id));


		if (!$query)

		{

			$could_not_change = "Sorry, you could not change your name! Please contact the admin!<br /><br />";

		}

		else

		{
			echo "Congratulations, you have changed your name!";
			exit;

		}

	}

}

$msg1 .= "</font>"; //Username error?




?>

<?=$msg1?>
<?=$could_not_change?>

<form method="post" action="custom.php?custom=namechange">

<p><label for='newname'>Name</label><input type="text" name="newname" value="<?=$player->username?>" />

<p class='submit'><label for='kludge'></label><input type="submit" value="Change it!">

</form>

<?php

		include_once("templates/themes/" . $setting->theme . "/private_footer.php");

		break;
		


case "sigchange":
		
		$player = check_user($secret_key, $db);
		
		include_once("templates/themes/" . $setting->theme . "/private_header.php");
		
		echo "<fieldset>";

		echo "<legend><b>Player Customization</b></legend>";

		echo "<center><br /><input type='button' VALUE='Change Avatar' ONCLICK=\"window.location.href='custom.php?custom=avatar'\">";
		
		echo "<input type='button' VALUE='Change Name' ONCLICK=\"window.location.href='custom.php?custom=namechange'\">";
		
		echo "<input type='button' VALUE='Change Password' ONCLICK=\"window.location.href='custom.php?custom=passchange'\"><br /><br />";
		
		echo "<input type='button' VALUE='Change Email' ONCLICK=\"window.location.href='custom.php?custom=emailchange'\">";
		
		echo "<input type='button' VALUE='Change Signature' ONCLICK=\"window.location.href='custom.php?custom=sigchange'\">";

		echo "<input type='button' VALUE='Change Gender' ONCLICK=\"window.location.href='custom.php?custom=genderchange'\"></center>";
		
		echo "<br>";

		echo "</fieldset><br /><br />";
		
		echo "<fieldset>";
		
		echo "<legend>Signature Change</legend>";


$msg1 = "<font color=\"red\">"; //Username error?

$error = 0;


if ($_POST['newsig'])

{

	
	//Check sig

	if (!$_POST['newsig']) 
	
	{ //If signature isn't filled in...

		$msg1 .= "You need to fill in your new signature!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}

	else if (strlen($_POST['newsig']) < $minsig)

	{ //If signature is too short...

		$msg1 .= "Your signature must be longer than ".$minsig." characters!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}
	
	else if (strlen($_POST['newsig']) > $maxsig)

	{ //If signature is too short...

		$msg1 .= "Your signature must be less than ".$maxsig." characters!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}

	else if (!preg_match("/^[-_a-zA-Z\s*0-9.?!]+$/", $_POST['newsig']))

	{ //If signature contains illegal characters...

		$msg1 .= "Your signature may contains only alphanumerical characters!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}


	if ($error == 0)

	{

	$query = $db->execute("update `players` set `sig`=? where id=?", array($_POST['newsig'], $player->id));


		if (!$query)

		{

			$could_not_change = "Sorry, you could not change your signature! Please contact the admin!<br /><br />";

		}

		else

		{
			echo "Congratulations, you have changed your signature!";
			exit;

		}

	}

}

$msg1 .= "</font>"; //Username error?




?>

<?=$msg1?>
<?=$could_not_change?>

<form method="post" action="custom.php?custom=sigchange">

<p><label for='newsig'>Signature</label>

<textarea name="newsig" cols="40" rows="7" id="newsig"><?=$player->sig?></textarea></p>

<p class='submit'><label for='kludge'></label><input type="submit" value="Change it!">

</form>


<?php

		include_once("templates/themes/" . $setting->theme . "/private_footer.php");

		break;



		
		

case "emailchange":


		$player = check_user($secret_key, $db);
		
		include_once("templates/themes/" . $setting->theme . "/private_header.php");
		
		echo "<fieldset>";

		echo "<legend><b>Player Customization</b></legend>";

		echo "<center><br /><input type='button' VALUE='Change Avatar' ONCLICK=\"window.location.href='custom.php?custom=avatar'\">";
		
		echo "<input type='button' VALUE='Change Name' ONCLICK=\"window.location.href='custom.php?custom=namechange'\">";
		
		echo "<input type='button' VALUE='Change Password' ONCLICK=\"window.location.href='custom.php?custom=passchange'\"><br /><br />";
		
		echo "<input type='button' VALUE='Change Email' ONCLICK=\"window.location.href='custom.php?custom=emailchange'\">";
		
		echo "<input type='button' VALUE='Change Signature' ONCLICK=\"window.location.href='custom.php?custom=sigchange'\">";

		echo "<input type='button' VALUE='Change Gender' ONCLICK=\"window.location.href='custom.php?custom=genderchange'\"></center>";
		
		echo "<br>";

		echo "</fieldset><br /><br />";
		
		echo "<fieldset>";
		
		echo "<legend>Email Change</legend>";


$msg1 = "<font color=\"red\">"; //Username error?

$error = 0;


if ($_POST['newemail'])

{
	//Check if email has already been used

	$query = $db->execute("select email from `players` where `email`=?", array($_POST['newemail']));
	
	//Check username
  	$regexp="/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";

	if (!$_POST['newemail']) 
	
	{ //If email isn't filled in...

		$msg1 .= "You need to fill in your new email!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}
	
	
	elseif ( !preg_match($regexp, $_POST['newemail']) ) 
	{
		$msg1 .= "Your email contains illegal characters!<br />\n"; //Add to error message

		$error = 1; //Set error check
  	}


	else if (strlen($_POST['newemail']) < 9)

	{ //If username is too short...

		$msg1 .= "Your email must be longer than 9 characters!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}



	else if ($query->recordcount() > 0)

	{

		$msg1 .= "That email has already been used.<br />\n";

		$error = 1; //Set error check

	}

	

	if ($error == 0)

	{

	$query = $db->execute("update `players` set `email`=? where id=?", array($_POST['newemail'], $player->id));


		if (!$query)

		{

			$could_not_change = "Sorry, you could not change your email! Please contact the admin!<br /><br />";

		}

		else

		{
			echo "Congratulations, you have changed your email!";
			exit;

		}

	}

}

$msg1 .= "</font>"; //email error?




?>

<?=$msg1?>
<?=$could_not_change?>

<form method="post" action="custom.php?custom=emailchange">

<p><label for='newname'>Your Email</label><input type="text" style="width:250px;" name="newemail" value="<?=$player->email?>" />

<p class='submit'><label for='kludge'></label><input type="submit" value="Change it!">

</form>

<?php

		include_once("templates/themes/" . $setting->theme . "/private_footer.php");

		break;
		
		
		
		
case "genderchange":
		
		$player = check_user($secret_key, $db);
		
		include_once("templates/themes/" . $setting->theme . "/private_header.php");
		
		echo "<fieldset>";

		echo "<legend><b>Player Customization</b></legend>";

		echo "<center><br /><input type='button' VALUE='Change Avatar' ONCLICK=\"window.location.href='custom.php?custom=avatar'\">";
		
		echo "<input type='button' VALUE='Change Name' ONCLICK=\"window.location.href='custom.php?custom=namechange'\">";
		
		echo "<input type='button' VALUE='Change Password' ONCLICK=\"window.location.href='custom.php?custom=passchange'\"><br /><br />";
		
		echo "<input type='button' VALUE='Change Email' ONCLICK=\"window.location.href='custom.php?custom=emailchange'\">";
		
		echo "<input type='button' VALUE='Change Signature' ONCLICK=\"window.location.href='custom.php?custom=sigchange'\">";

		echo "<input type='button' VALUE='Change Gender' ONCLICK=\"window.location.href='custom.php?custom=genderchange'\"></center>";
		
		echo "<br>";

		echo "</fieldset><br /><br />";
		
		echo "<fieldset>";
		
		echo "<legend>Gender Change</legend>";


if ($_POST['newgender'])

{


	$query = $db->execute("update `players` set `gender`=? where id=?", array($_POST['newgender'], $player->id));


		if (!$query)

		{

			$could_not_change = "Sorry, you could not change your gender! Please contact the admin!<br /><br />";

		}

		else

		{
			echo "Congratulations, you have changed your gender!";
			exit;

		}
	

}


?>

<?=$msg1?>
<?=$could_not_change?>

<form method="post" action="custom.php?custom=genderchange">

<?php

	echo "<label for='newgender'>Your Gender</label>";
	echo "<select name=\"newgender\"><option value=\"Male\"";
	echo ($player->gender == 'Male')?" selected=\"selected\"":"";
	echo ">Male</option>";
	echo "<option value=\"Female\"";
	echo ($player->gender == 'Female')?" selected=\"selected\"":"";
	echo ">Female</option>";
	echo "<option value=\"?\"";
	echo ($player->gender == '?')?" selected=\"selected\"":"";
	echo ">?</option>";
	echo "</select>";
?>

<p class='submit'><label for='kludge'></label><input type="submit" value="Change it!">

</form>

<?php

include_once("templates/themes/" . $setting->theme . "/private_footer.php");

break;
		
		
		
		
		
		case "passchange":
		
		$player = check_user($secret_key, $db);
		
		include_once("templates/themes/" . $setting->theme . "/private_header.php");
		
		echo "<fieldset>";

		echo "<legend><b>Player Customization</b></legend>";

		echo "<center><br /><input type='button' VALUE='Change Avatar' ONCLICK=\"window.location.href='custom.php?custom=avatar'\">";
		
		echo "<input type='button' VALUE='Change Name' ONCLICK=\"window.location.href='custom.php?custom=namechange'\">";
		
		echo "<input type='button' VALUE='Change Password' ONCLICK=\"window.location.href='custom.php?custom=passchange'\"><br /><br />";
		
		echo "<input type='button' VALUE='Change Email' ONCLICK=\"window.location.href='custom.php?custom=emailchange'\">";
		
		echo "<input type='button' VALUE='Change Signature' ONCLICK=\"window.location.href='custom.php?custom=sigchange'\">";

		echo "<input type='button' VALUE='Change Gender' ONCLICK=\"window.location.href='custom.php?custom=genderchange'\"></center>";
		
		echo "<br>";

		echo "</fieldset><br /><br />";		
		echo "<fieldset>";
		
		echo "<legend>Password Change</legend>";


$msg1 = "<font color=\"red\">"; //Username error?

$error = 0;




if ($_POST['newpass'])

{

	if (!$_POST['newpass'])
	
	{ //If pass isn't filled in...

		$msg1 .= "You need to fill in your new Password!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}

	else if (strlen($_POST['newpass']) < 6)

	{ //If username is too short...

		$msg1 .= "Your Password must be longer than 3 characters!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}

	else if (!preg_match("/^[-_a-zA-Z0-9]+$/", $_POST['newpass']))

	{ //If username contains illegal characters...

		$msg1 .= "Your Password may contain only alphanumerical characters!<br />\n"; //Add to error message

		$error = 1; //Set error check

	}

	

	if ($error == 0)

	{
$newpass=sha1($_POST['newpass']);

	$query = $db->execute("update `players` set `password`=? where id=?", array($newpass, $player->id));


		if (!$query)

		{

			echo "Sorry, you could not change your name! Please contact the admin!<br /><br />";
			exit;
		}

		else

		{
			echo "Congratulations, you have changed your Password!";
			exit;

		}

	}
	
}
$msg1 .= "</font>"; //Username error?

?>


<?=$msg1?>
<?=$could_not_change?>

<form method="post" action="custom.php?custom=passchange">

<p><label for='newname'>New Password</label><input type="password" name="newpass" value="" />

<p class='submit'><label for='kludge'></label><input type="submit" value="Change it!">

</form>

<?php

include_once("templates/themes/" . $setting->theme . "/private_footer.php");

break;
	




	default:

		include_once("templates/themes/" . $setting->theme . "/private_header.php");
		
		echo "<fieldset>";

		echo "<legend><b>Player Customization</b></legend>";

		echo "<center><br /><input type='button' VALUE='Change Avatar' ONCLICK=\"window.location.href='custom.php?custom=avatar'\">";
		
		echo "<input type='button' VALUE='Change Name' ONCLICK=\"window.location.href='custom.php?custom=namechange'\">";
		
		echo "<input type='button' VALUE='Change Password' ONCLICK=\"window.location.href='custom.php?custom=passchange'\"><br /><br />";
		
		echo "<input type='button' VALUE='Change Email' ONCLICK=\"window.location.href='custom.php?custom=emailchange'\">";
		
		echo "<input type='button' VALUE='Change Signature' ONCLICK=\"window.location.href='custom.php?custom=sigchange'\">";

		echo "<input type='button' VALUE='Change Gender' ONCLICK=\"window.location.href='custom.php?custom=genderchange'\"></center>";
		
		echo "<br>";

		echo "</fieldset><br /><br />";
		


		include_once("templates/themes/" . $setting->theme . "/private_footer.php");

		break;

}

?>