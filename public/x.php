<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>

<body>

<?php
/*  x.php
    Last update: 4/19/07

    The purpose of this file is to process the information from the user's mapping input.
    The database is updated, then the results are queried and printed to
*/
include('../includes/sqlConnect.php');
include '../includes/standardize.php';

$xCoord = $_POST['x'];
$yCoord = $_POST['y'];

sqlConnect();

if (isset($_POST['location_id']))
{
	$set_mode = "location";
	$location_id = $_POST['location_id'];
	$stackNo = 0;	// We use range number 0 as the placeholder to store the x-y position of the location in this map

	// check the total ranges (must be either one or zero)
	$sql = "SELECT count(*) as total_ranges from stacks_$location_id";
	$result = mysql_query($sql);
	$total_ranges = mysql_result($result, 0);

	// insert a new row in stacks_locid, so that later it can be updated with the new map coordinate values.
	if ($total_ranges == 0)
	{
		$query = "INSERT INTO `stacks_$location_id` ( `beginning_call_number` , `ending_call_number` , `range_number`, `std_beg`, `std_end` )
		      VALUES ('*', '*', '0', '".standardize('0')."', '".standardize('Z', true)."');";
		$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	}
	else if ($total_ranges > 1)
	{
		die("Error encountered: You shouldn't arrive here. Total ranges should not be more than one if you want to assign a location to this map.");
	}

}
else if (isset($_POST['stackNo']))
{
	$set_mode = "range";
	$stackNo = $_POST['stackNo'];

	/* A map must be selected as current to assign icons to it. */
	$sql3 = mysql_query("SELECT location_id FROM current");
	if($row3 = mysql_fetch_array($sql3))
		$location_id = $row3['location_id'];
	else
		$location_id ="";
}
else
{
	die("Error: Parameter incorrect.");
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

$currenticon = 1;

$sql2 = mysql_query("SELECT filename FROM iconassign, iconimgs WHERE iconid = icoid AND mapid = ".$mapid);
if($row2 = mysql_fetch_array($sql2))
  $iconfile = $row2['filename'];
else
  $iconfile ="";

  /*  Fill the stack table with the x and y coordinates of the user's click  */


  $query = "UPDATE stacks_$location_id SET x_coord = $xCoord WHERE range_number = $stackNo;";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());

	$query = "UPDATE stacks_$location_id SET y_coord = $yCoord WHERE range_number = $stackNo;";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());

  /* Query the database to ensure the data was added correctly */

$xycoords = mysql_query("SELECT x_coord, y_coord FROM stacks_$location_id WHERE range_number = \"$stackNo\"");

    while($row = mysql_fetch_array($xycoords)){
      $img_x_coord = $row['x_coord'];
      $img_y_coord = $row['y_coord'];
      }

    /*  This 111 pixel offest is used to move the marker down from its original mapping.  The (x,y) coordinates
        stored in the database were relative to the image only, not the image plus the text.  This might need
        to be changed if the content above the image is modified. */

    $img_y_coord -= 10;
	$img_x_coord -= 10;

    /* Display feedback to the user including the map with a marker where he/she clicked */

PRINT<<<END
<style media="screen">
END;
echo  ".noprint { position:absolute; top:".$img_y_coord."px; left:".$img_x_coord."px; }";
echo  ".noscreen { display:none; }";
PRINT<<<END
</style>
END;

	if ($set_mode == "location")
	{
		echo "<strong>Entry successfully added for location $location!</strong>";
	}
	else if ($set_mode == "range")
	{
		echo "<strong>Entry successfully added for range $stackNo!</strong>";
	}
	echo "<br />";
	echo "<a href='javascript:window.close()'>Close Window</a>";
	echo "<br /><br />";

  echo "<div style=\"position:absolute; \"> <img src=\"./maps/$mapfile\" alt=\"Library stack map\" style=\"\" />\n";

  /* Star placement for the computer screen */

  echo "<img class='noprint' src=\"./icons/$iconfile\" alt=\"Map marker\" />";
  echo "</div>\n";



?>

</body>
</html>
