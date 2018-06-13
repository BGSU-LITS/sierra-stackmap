<?php
/*  stacks.php
    Last update: 5/23/07
    Last modified by: Woon (woonkhang@gmail.com)
    Changes: Change sql statement so that the ranges are printed in order of range number
    
    This file has functions included for the purposes of the index.php Stack Map Admin control panel.
    stacks.php has a number of functions called by index.php's main menu that relate to printing and 
    managing a library's stack ranges as they pertain to a specific map.
*/


/*  Print out a list of all stack and call number ranges for the current map.  Allows for editing and deletion of 
    stack entries. */

function stackList()
{
	$location_id = getCurrentLocationID();
	if ($location_id == "") {
		return errorNoMapSelected();
	}
	
	$query = "select * from stacks_$location_id ORDER BY range_number";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	
	/* Begin a table to contain the stack records */
	
	$dynamicText = "<table cellpadding=\"0\" cellspacing=\"0\" id=\"stackList\"><tr><th>Range Number</th><th>Beginning Call No.</th><th>End Call No.</th><th colspan=\"3\">&nbsp;</th></tr>";
	
	/* For each range number found... */
	
	$i = 0;
	while ($d = mysql_fetch_array($result))
	{
		$number = $d['range_number'];
		$begin = $d['beginning_call_number'];
		$end = $d['ending_call_number'];

    /* Calculation to decide background color of each row */

		if ( ($i % 2) == 1 )
			$color = "ffffff";
		else
			$color = "fff0cf";
		
		// not yet assign a map coordinate
		if ($d['x_coord'] == 0 && $d['y_coord'] == 0)
			$color = "d3dce3";
			
		$dynamicText .= "<tr style=\"background:#$color;\">
		<td>$number</td><td>$begin</td>
		<td>$end</td>
		<td><a onclick=\"window.open('../coor.php?range=".$number."')\" href=\"#\"><img border=\"0\" src=\"mapit.gif\" title=\"MapIt!\" /></a></td>
		<td><a href=\"index.php?section=stacks&mode=edit&i=$number\"><img border=\"0\" src=\"edit.png\" title=\"edit\"></a></td>
		<td><a href=\"index.php?section=stacks&mode=delete&i=$number\"><img border=\"0\"src=\"delete.png\" title=\"delete\"></a></td>
		</tr>";
		
		$i++;
	}
	
	$dynamicText .= "</table>";
	
	return $dynamicText;
}



/*  Displays a form for adding new stack ranges to the database.  Specifically, this adds new ranges to the
    current map. */

function addRange()
{
	$location_id = getCurrentLocationID();
	if ($location_id == "") {
		return errorNoMapSelected();
	}
	
	$dynamic = "<form action=\"index.php?section=stacks&mode=processaddrange\" method=\"get\">";
	$dynamic .=	"<fieldset><legend>Add a range</legend>";
	$dynamic .= "<label for=\"begin\">Range No.:&nbsp;</label><input type=\"text\" size=\"25\" name=\"range\" id=\"begin\" value=\"\" /><br />";
	$dynamic .= "<label for=\"begin\">Beginning Call No.:&nbsp;</label><input type=\"text\" size=\"25\" name=\"begin\" id=\"begin\" value=\"\" /><br />";
	$dynamic .= "<label for=\"end\">Ending Call No.:&nbsp;<input style=\"padding-left:17px\" type=\"text\" size=\"25\" name=\"end\" id=\"end\" value=\"\" /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"section\" id=\"hidden\" value=\"stacks\" /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"mode\" id=\"hidden\" value=\"processaddrange\" /><br />";
  $dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Add range\" />";
	$dynamic .= "</fieldset></form>";
	
	return $dynamic;
}


/* Adds the new range into the database and provides user feedback */

function processAddRange(){
$range = $_GET["range"];
$begin = $_GET["begin"];
$end = $_GET["end"];
$begin_stan = $begin;
$end_stan = $end;

if ($begin == "" || $begin == "*")
{
	$begin = "*";
	$begin_stan = "0";
}
if ($end == "" || $end == "*")
{
	$end = "*";
	$end_stan = "Z";
}


sqlConnect();

/*  Remember -- the range is added to the current map */

$location_id = getCurrentLocationID();

/*  Note that the call numbers are run through standardize(), which is in standardize.php to
    put the call numbers into searchable format.  See that file for more details. */

$query = "INSERT INTO `stacks_$location_id` ( `beginning_call_number` , `ending_call_number` , `range_number`, `std_beg`, `std_end` )
      VALUES ('$begin', '$end', '$range', '".standardize($begin_stan)."', '".standardize($end_stan, true)."');";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());

$dynamic = "<p>Successfully added range ".$range." (".$begin."-".$end.").</p>";

return $dynamic;
}


/*  After a user clicks the delete icon on a range, he/she is presented with this
    confirmation form */

function deleteRange()
{
	
	$index = $_GET['i'];
	
	$dynamic = "<form action=\"index.php?section=stacks&mode=processdeleterange\" method=\"get\">";
	$dynamic .=	"<fieldset><legend>Confirm delete</legend>";
	$dynamic .= "<label for=\"begin\">Are you sure you want to delete range $index?&nbsp;</label><br /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"i\" id=\"hidden\" value=\"$index\" /><br />";
	$dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Yes\" />";
	$dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"No\" />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"section\" id=\"hidden\" value=\"stacks\" /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"mode\" id=\"hidden\" value=\"processdeleterange\" /><br />";
	$dynamic .= "</fieldset></form>";
	
	return $dynamic;
}


/*  The delete is processed, and if successful, the database record is deleted */

function processDeleteRange(){
$range = $_GET["i"];

if($_GET['submit'] == "No")
{
$dynamic = "<p>Delete canceled.</p>";
}
else{

sqlConnect();

$location_id = getCurrentLocationID();

$query = "DELETE FROM `stacks_$location_id` WHERE `range_number` = $range LIMIT 1";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());

$dynamic = $dynamic = "<p>Range ".$range." has been successfully deleted.</p>";
}
return $dynamic;

}


/*  A user can click the edit button from the 'view' screen to bring up this form.  The form
    displays the current settings in the fields, which the user can then edit. */

function editRange()
{
	sqlConnect();
	
	$index = $_GET['i'];

  $location_id = getCurrentLocationID();
	
	$query = "select * from stacks_$location_id where range_number = $index";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	$d = mysql_fetch_array($result);
	
	$begin = $d['beginning_call_number'];
	$end = $d['ending_call_number'];
	
	$dynamic = "<form action=\"index.php\" method=\"get\">";
	$dynamic .=	"<fieldset><legend>Details for Stack No. $index</legend>";
	$dynamic .= "<label for=\"begin\">Beginning Call No.:&nbsp;</label><input type=\"text\" size=\"25\" name=\"begin\" id=\"begin\" value=\"$begin\" /><br />";
	$dynamic .= "<label for=\"end\">Ending Call No.:&nbsp;<input style=\"padding-left:17px\" type=\"text\" size=\"25\" name=\"end\" id=\"end\" value=\"$end\" /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"section\" id=\"hidden\" value=\"stacks\" /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"mode\" id=\"hidden\" value=\"processeditrange\" /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"i\" id=\"hidden\" value=\"$index\" /><br />";
  $dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Update\" />";
	$dynamic .= "</fieldset></form>";
  return $dynamic;
}		


/* Updates the database and provides feedback when the stack ranges have been modified */

function processEditRange(){
$range = $_GET["i"];

sqlConnect();
$location_id = getCurrentLocationID();

$begin = $_GET['begin'];
$end = $_GET['end'];
$begin_stan = $begin;
$end_stan = $end;

if ($begin == "" || $begin == "*")
{
	$begin = "*";
	$begin_stan = "0";
}
if ($end == "" || $end == "*")
{
	$end = "*";
	$end_stan = "Z";
}

$query = "UPDATE `stacks_$location_id` SET `beginning_call_number` = '".$begin."', `ending_call_number` = '".$end."' WHERE `range_number` =".$range." LIMIT 1";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());

$query = "UPDATE `stacks_$location_id` SET `std_beg` = '".standardize($begin_stan)."', `std_end` = '".standardize($end_stan, true)."' WHERE `range_number` =".$range." LIMIT 1";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());

$dynamic = $dynamic = "<p>Range ".$range." has been successfully updated.</p>";

return $dynamic;

}






?>
