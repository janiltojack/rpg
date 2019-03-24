<?php
####Form validation using Php
#files-class.FormValidation.php,formcss.css,form.php
##Validfield function
####By Sagar Sarkar 03/03/2008
##if you Find any Bug  please mail me at sarkar.sagar@gmail.com
######Parameters Details If ValidField Function###################
###function ValidField($Value,$CType,$ErrText="Invalid Error Type",$Params=array('Default'=>'','Min'=>false,'Max'=>false));
#$Value=Value after posting the form.
#$Ctype=Checking Type(text/email/empty/null/numeric)
#$Min=Minimum value required(only for text or numeric type checking)
#Max=Maximum value required(only for text or numeric type checking)
#$ErrText=Display message when error occured.
#Params=Optional i.e Default field value for bypass the valdiation criteria,minimum/maximum value length
###########################////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\#######################
include "lib/class.FormValidation.php";
if(count($_POST)>0){
$Form->ValidField($Name,'empty','Enter Your Name');
$Form->ValidField($Address,'empty','Enter your Address');
$Form->ValidField($Phone,'numeric','Enter Phone Number',array('Min'=>5,'Max'=>15));
$Form->ValidField($email,'email','Email Field is Empty Or Invalid');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<link href="formcss.css" rel="stylesheet" type="text/css" />
</head>

<body>
<? echo $Form->ErrorString.$Form->ErrSufix;?>
<br />
<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
<table width="90%">
  <tr>
    <td width="41%">
	

	<label>Enter Name 
        <input name="Name" type="text" id="Name"  value="<?=$Name?>"/> 
    </label></td>
    <td width="59%">check1<input type="checkbox" name="checkboxarray[]" value="checkbox1" />
      check2
      <input type="checkbox" name="checkboxarray[]" value="checkbox2" />
      check3
      <input type="checkbox" name="checkboxarray[]" value="checkbox3" />
      check4
      <input type="checkbox" name="checkboxarray[]" value="checkbox4" /></td>
  </tr>
  <tr>
    <td><p>Enter Phone
        <input name="Phone" type="text" id="Phone" value="<?=$Phone?>" />
</p>      </td>
    <td><label>
      <input name="radiobutton" type="radio" value="radiobuttonvalue" />
      radio</label></td>
  </tr>
  <tr>
    <td>Enter Email
      <input name="email" type="text" id="email"  value="<?=$email?>"/></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><label>Address
        <textarea name="Address" id="Address"></textarea>
    </label></td>
    <td><p>
      <label>
        <input type="radio" name="RadioGroup1" value="radio" />
        Radio</label>
      <br />
      <label>
        <input type="radio" name="RadioGroup1" value="radio" />
        Radio</label>
      <br />
      <label>
        <input type="radio" name="RadioGroup1" value="radio" />
        Radio</label>
      <br />
      <label>
        <input type="radio" name="RadioGroup1" value="radio" />
        Radio</label>
      <br />
    </p></td>
  </tr>
  <tr>
    <td><label>
      <input type="checkbox" name="checkbox" value="checkboxvalue" />
      I Accept Terms And Conditions </label></td>
    <td><label>select
        <select name="select">
		<option value="0">select</option>
		<option value="1">opt1</option>
		<option value="2">opt2</option>
		<option value="3">opt3</option>
        </select>
    </label></td>
  </tr>
  <tr>
    <td><input type="file" name="file" /></td>
    <td><label>button
        <input type="submit" name="Submit" value="Submit" />
    </label></td>
  </tr>
  <tr>
    <td colspan="2" align="center"></td>
    </tr>
  <tr>
    <td colspan="2" align="left">	   </td>
  </tr>
  <tr>
    <td colspan="2"></td>
    </tr>
</table>
</form>
</body>
</html>
