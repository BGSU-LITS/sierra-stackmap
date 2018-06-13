<?php
/*  maps.php
    Last update: 4/16/07
    
    This file has functions included for the purposes of the index.php Stack Map Admin control panel.
    maps.php has a number of functions called by index.php's main menu that relate to printing and 
    managing a library's maps as they pertain to a specific location.
*/

/* Create a table to list all the maps, their locations, and sizes.  Alow for editing and deletion. */

function mapList()
{
	sqlConnect();
	
	$query = "select * from mapimgs";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	
	$dynamicText = "<table cellpadding=\"0\" cellspacing=\"0\" id=\"stackList\"><tr><th>Map name</th><th>File name</th><th>File size</th><th>View file</th><th colspan=\"2\">&nbsp;</th></tr>";

	$number = 1;
	
	while ($d = mysql_fetch_array($result))
	{
		$name = $d['name'];
		$filename = $d['filename'];
		$mapid = $d['mapid'];

    /*  File size is calculated in kilobytes */

    $size = (filesize("../maps/".$filename))/1000;

    /* This code is for the table -- it determines the color based on whether it's an odd or even listing */

		if ( ($number % 2) == 1 )
			$color = "ffffff";
		else
			$color = "fffaef";
			
		$dynamicText .= "<tr style=\"background:#$color;\"><td>$name</td><td>$filename</td><td>$size K</td><td><a href=\"../maps/$filename\">View</a></td><td><a href=\"index.php?section=maps&mode=delete&i=$mapid\"><img border=\"0\"src=\"delete.png\"></a></td></tr>";
    $number++;
	}
	
	$dynamicText .= "</table>";
	
	return $dynamicText;
}

/* Form for uploading a new map file */

function uploadMap()
{


  $dynamic = "<form enctype=\"multipart/form-data\" action=\"index.php?section=maps&mode=processupload\" method=\"post\">";
	$dynamic .=	"<fieldset><legend>Upload map</legend>";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"MAX_FILE_SIZE\" id=\"hidden\" value=\"1000000\" /><br />";
	$dynamic .= "<label for=\"upload\">File:&nbsp;</label><input type=\"file\" size=\"25\" name=\"uploadedfile\" id=\"upload\" value=\"\" /><br />";
  $dynamic .= "<label for=\"mapname\">Map name:&nbsp;</label><input type=\"text\" size=\"25\" name=\"mapname\" id=\"mapname\" value=\"\" /><br />";
  $dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Upload file\" />";
	$dynamic .= "</fieldset></form>";
  return $dynamic;
}


/* Confirms the user's delete selection */

function deleteMap()
{
	
	$index = $_GET['i'];
	
	
	$dynamic = "<form action=\"index.php?section=maps&mode=processdelete&i=$index\" method=\"post\">";
	$dynamic .=	"<fieldset><legend>Confirm delete</legend>";
    $dynamic .= "<label for=\"begin\">Are you sure you want to delete this map?&nbsp;</label><br /><br />";
	$dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Yes\" />";
	$dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"No\" />";
	$dynamic .= "</fieldset></form>";
	
	return $dynamic;
}

/* Sends user away to the coor.php interface */
/*
function assignCoords(){
	$mapid = getCurrentMap();
	if ($mapid == "") {
		return errorNoMapSelected();
	}
	$dynamic = '<script type="text/javascript">';
	$dynamic .= 'window.open("../coor.php")';
	$dynamic .= '</script>';
	return $dynamic;
header("Location: ../coor.php");

}
*/
/*  Interface for selecting the "current map" that displays on every page under the menu.
    Creates a simple dropdown of available menus and shows the one that is currently
    selected as the default. */

function selectLocation()
{
	$dynamic = "";

	/*$dynamic .= "<p>Please select the map that the system should consider as \"current.\"  This will make it the only map updated/displayed 
	for stack charts, coordinate asignments, and utility queries. </p><br />";
	*/
	sqlConnect();

	//$query2 = "select * from mapimgs";
	$query2 = "select * from maps order by location";
	$result2 = mysql_query($query2) or die("Invalid query: " . mysql_error());

	//$query3 = "select mapid from current";
	$query3 = "select location_id from current";
	$result3 = mysql_query($query3) or die("Invalid query: " . mysql_error());
	$e = mysql_fetch_array($result3);	

	$dynamic .= "<form id=\"currentMap\" enctype=\"multipart/form-data\" action=\"index.php\" method=\"get\">";
	$dynamic .= "<select name=\"currentmap\" onchange=\"document.getElementById('currentMap').submit()\">";
	$dynamic .= "<option value=\"nomap\" selected=\"selected\">Please select a location...</option>";

	while ($c = mysql_fetch_array($result2)) {
		$location = $c['location'];
			
		if($c['location_id'] == $e['location_id']) {
			$selected = "selected = \"selected\"";
	    }
	    else {
			$selected = "";
	    }
		
		$dynamic .= "<option value=\"$location\" $selected>&nbsp;&nbsp;&nbsp;$location</option>";  
	}
	$dynamic .= "</select>";
	
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"section\" id=\"hidden\" value=\"maps\" />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"mode\" id=\"hidden\" value=\"processcurrentselect\" />";
	//$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"map\" id=\"hidden\" value=\"".$c['name']."\" />";
	$dynamic .= "</form>";
	$dynamic .= "<br />";

	//$result = mysql_query($query2) or die("Invalid query: " . mysql_error());

	return $dynamic;
}


/*  Once a new current map is selected the database needs to be updated.  The user is given feedback
    about his/her selection too. */

function processSelect(){
	if ($_GET['currentmap'] == "nomap") {
		return;
	} 


sqlConnect();

$sql = mysql_query("SELECT location_id FROM maps WHERE location = \"".$_GET["currentmap"]."\"");
if($row = mysql_fetch_array($sql))
  $location_id = $row['location_id'];
else
  $location_id = "";

$sql = mysql_query("UPDATE `current` SET `location_id` = ".$location_id." WHERE `location_id` is not null LIMIT 1") 
or die("Invalid query: " . mysql_error());


$dynamic = "<p>".$_GET["currentmap"]." has been successfully set as the current location. </p><br />";


return $dynamic;

}




?>
