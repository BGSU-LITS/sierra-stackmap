<?php
/*  coor.php
    Last update: 4/23/07

    The purpose of this file is to display the current map and allow the user to assign coordinates
    to a stack/library map.
*/
include('../includes/sqlConnect.php');

$link = sqlConnect();

/*  Retrieve the file for the current map */

// set the location (ie, all ranges in the location) to a map
if (isset($_GET['location_id']))
{
	$set_mode = "location";
	$location_id = $_GET['location_id'];

	if (isset($_GET['error']))
		die("Some ranges are already assigned to this location.<br />Please delete all ranges first if you want to assign this location to this map.");

}
// set the range location to a map
else if (isset($_GET['range']))
{
	$set_mode = "range";
	$range = $_GET['range'];
	$sql = mysql_query("SELECT location_id FROM current");
	if($row = mysql_fetch_array($sql))
	  $location_id = $row['location_id'];
	else
	  $location_id ="";
}

$result = mysql_query("SELECT mapid, location FROM maps WHERE location_id = ".$location_id);
$row = mysql_fetch_array($result);
$mapid = $row['mapid'];
$location = $row['location'];

$sql = mysql_query("SELECT filename FROM mapimgs WHERE mapid = ".$mapid);
if($row = mysql_fetch_array($sql))
  $mapfile = $row['filename'];
else
  $mapfile ="";


if ($set_mode == "location")
{
$instructions = "<b>You've selected location: $location.</b><br>
<p style='color:red; font-weight:bold'> Please click on the map location where you would like that location to be assigned to.</p>";

}
else if ($set_mode == "range")
{
$instructions = "<b>You've selected range no: $range.</b><br>
<p style='color:red; font-weight:bold'> Please click on the map location where you would like that range number to be assigned to.
For example, to assign range 1 to a section on the map, simply click in the middle of range 1 on the map below.</p>";

}

/*  Print the coordinate assignment interface.  Note that the form posts to x.php. */

print <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>

<body>
$instructions

<form action="x.php" method="post">
<table>
	<tr>
END;

/* Print the dynamically retrieved map file */

echo "<td><input type=\"image\" src=\"./maps/".$mapfile."\" value=\"Submit\" alt=\"Submit\"></td>";

if ($set_mode == "location")
{
	echo "<input type=\"hidden\" name=\"location_id\" value=\"$location_id\" />";
}
else if ($set_mode == "range")
{
	echo "<input type=\"hidden\" name=\"stackNo\" value=\"$range\" />";
}

print<<<END
	</tr>
</table>

</form>

<br />


</body>
</html>
END;
?>
