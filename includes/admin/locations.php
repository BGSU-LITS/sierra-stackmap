<?php
/*  locations.php
    Last update: 5/23/07
    Last modified by: Woon (woonkhang@gmail.com)
    Changes: Change sql statement so that the locations are printed in order of location name
    
    This file has functions included for the purposes of the index.php Stack Map Admin control panel.
    locations.php has a number of functions called by index.php's main menu that relate to printing and 
    managing a library's various locations within (or even outside) the library.
*/

/*    Creates the table of locations and displays them for viewing and edit/deletion purposes */

// validates if the location is able to be map into 1 single location (it's true when there's no ranges assigned to that specific location)
function validateLocation($location_id)
{
	// check to make sure no ranges exist in the location
	$sql = "SELECT count(*) as total_ranges from stacks_$location_id";
	$result = mysql_query($sql);
	$total_ranges = mysql_result($result, 0);
	if ($total_ranges > 1)	// more than 1 range, don't mess with it
	{
		return false;
	}
	else
	{
		if ($total_ranges == 1)		// if there's 1 range, make sure it's not assign to a specific call number range
		{
			$sql = "SELECT beginning_call_number, ending_call_number from stacks_$location_id";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);	
			if ($row['beginning_call_number'] != "*" || $row['ending_call_number'] != "*")
			{
				return false;
			}
		}
	}
	
	// returns true, it's either 0 range or 1 range with all call numbers range assign to one single location
	return true;
}

function printLocations()
{
	sqlConnect();

  /* Needed to check whether a map has been assigned to a particular location */

	$query = "select * from maps ORDER BY location";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	
	$index = 0;

  /* Begin the table */
	
	$dynamicText = "<table cellpadding=\"0\" cellspacing=\"0\" id=\"stackList\"><tr><th>Name</th><th>Link</th><th>Map</th><th colspan=\"3\">&nbsp;</th></tr>";
	
  /* For each location... */

	while ($d = mysql_fetch_array($result))
	{
		$location_id = $d['location_id'];
		$name = $d['location'];
		$link = $d['text_link'];
		$is_map = $d['is_mapfile'];

		if ( ($index % 2) == 1 )
			$color = "ffffff";
		else
			$color = "fffaef";

    /*  If there's a map associated, find out which one */
			
		if ($is_map){
		  $query3 = "select mapid from maps where location = '".$name."'";
	    $result3 = mysql_query($query3) or die("Invalid query: " . mysql_error());
	    $e = mysql_fetch_array($result3);
      $mapid = $e['mapid'];
		
      $query = "SELECT filename FROM mapimgs WHERE mapid = ".$mapid."";
      $result2 = mysql_query($query) or die("Invalid query: " . mysql_error());
      $d = mysql_fetch_array($result2);
      $is_map = $d['filename'];
    
    }
    else{
		$is_map = "N/A";
    }
			
		$dynamicText .= "<tr style=\"background:#$color;\"><td>$name</td><td><a href=\"".$link."\">".$link."</a></td><td>$is_map</td>";
		if ($is_map == "N/A")
		{
			$dynamicText .= "<td>&nbsp;</td>";
		}
		else
		{
			$dynamicText .= validateLocation($location_id) ? 
			"<td><a onclick=\"window.open('coor.php?location_id=$location_id')\" href=\"#\"><img border=\"0\" src=\"mapit.gif\" title=\"MapIt!\" /></a></td>" :
			"<td><a onclick=\"window.open('coor.php?location_id=$location_id&error=1')\" href=\"#\"><img border=\"0\" src=\"mapitgrey.jpg\" title=\"Some ranges are already assigned to this location. Please delete all ranges first if you want to assign this location to this map.\" /></a></td>";
		}
		$dynamicText .= "<td><a href=\"index.php?section=locations&mode=edit&i=$location_id\"><img border=\"0\" src=\"edit.png\"></a></td>
		<td><a href=\"index.php?section=locations&mode=delete&i=$location_id\"><img border=\"0\"src=\"delete.png\"></a></td></tr>";
	  $index++;
  }
	
	$dynamicText .= "</table>";
	
	return $dynamicText;
}


/* Prompts user yes/no regarding an location deletion */

function deleteLocation()
{
	$index = $_GET['i'];
	
	sqlConnect();
	$sql = "SELECT location from maps where location_id = $index";
	$result = mysql_query($sql);
	$location = mysql_result($result, 0);
		
	$dynamic = "<form action=\"index.php?section=locations&mode=processdelete&i=$index\" method=\"post\">";
	$dynamic .=	"<fieldset><legend>Confirm delete</legend>";
	$dynamic .= "<label for=\"begin\"><b>Warning:</b> You've selected to delete location: <b>$location</b></label><br />";
	$dynamic .= "<label for=\"begin\"><b>ALL RANGES</b> associated with this location will be <b>REMOVED</b> from the database!</label><br /><br />";
	$dynamic .= "<label for=\"begin\">Are you sure you want to proceed?&nbsp;</label><br /><br />";
	$dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Yes\" />";
	$dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"No\" />";
	$dynamic .= "</fieldset></form>";
	
	return $dynamic;
}



/* Creates a form for editing an already-existing location record */

function editLocation()
{
	sqlConnect();
	
	$index = $_GET['i'];

  /* Get the map and mapid based on the location name */

	$query = "select * from maps where location_id = '".$index."'";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	$d = mysql_fetch_array($result);
	
	$query2 = "select * from mapimgs";
	$result2 = mysql_query($query2) or die("Invalid query: " . mysql_error());
	
	$query4 = "select mapid from maps where location_id = '".$index."'";
	$result4 = mysql_query($query4) or die("Invalid query: " . mysql_error());
	$f = mysql_fetch_array($result4);
	$mapid = $f['mapid'];
	
	if(is_null($mapid)){
  $b = NULL;
  }
	else{
	$query3 = "select name from mapimgs where mapid = $mapid";
	$result3 = mysql_query($query3) or die("Invalid query: " . mysql_error());
	$b = mysql_fetch_array($result3);
	}
	
	$location = $d['location'];
	$link = $d['text_link'];
	
	/* Make a form and fill it with the pre-existing values for this record */
	
	$dynamic = "<form action=\"index.php\" method=\"get\">";
	$dynamic .=	"<fieldset><legend>Details for location: $location</legend>";
	$dynamic .= "<label for=\"begin\">Location Name:&nbsp;</label><input type=\"text\" size=\"25\" name=\"begin\" id=\"location\" value=\"$location\" /><br />";
	$dynamic .= "<label for=\"end\">Link:&nbsp;<input style=\"padding-left:17px\" type=\"text\" size=\"25\" name=\"link\" id=\"end\" value=\"$link\" /><br />";
	
  
              $dynamic .= "<label>Map:&nbsp;</label><select name=\"maps\">";
              $dynamic .= "<option value=\"None\">None</option>";
              
              /* Make the dropdown box with map names.  Show the currently selected map */         
            
              while ($c = mysql_fetch_array($result2))
            	{
            		$name = $c['name'];
            		
            		
            		
                if (is_null($b['name'])){
                $b['name'] = "None";
                }
            		
            	  if($name == $b['name']){
                $selected = "selected = \"selected\"";  
                }
                else{
                $selected = "";
                }
              $dynamic .= "<option value=\"$name\" $selected>$name</option>";
              }
            
            	$dynamic .= "</select>";
  
  /* Hidden variables that get sent to the next page */
  
  $dynamic .= "<input type=\"hidden\" size=\"25\" name=\"section\" id=\"hidden\" value=\"locations\" /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"mode\" id=\"hidden\" value=\"processedit\" /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"i\" id=\"hidden\" value=\"$index\" /><br />";
  $dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Update\" />";
	$dynamic .= "</fieldset></form>";
	
	return $dynamic;
}



/* Updates the database and provides feedback regarding the 'edit' that the user has submitted */

function processEditLocation(){
sqlConnect();


/* This first segment checks to see if (and what) map has been assigned to a location */

if($_GET['maps'] == "None"){
$is_map = 0;
$mapid = "NULL";
}
else{

$query = "select mapid from mapimgs where name = '".$_GET['maps']."'";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	$d = mysql_fetch_array($result);

$is_map = 1;
$mapid = $d['mapid'];
}

/* Update the database with the passed-in data */

$query = "UPDATE `maps` SET `location` = '".$_GET['begin']."', `text_link` = '".$_GET['link']."', `is_mapfile` = '".$is_map."', 
mapid = ".$mapid." WHERE `location_id` ='".$_GET['i']."' LIMIT 1";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());


$dynamic =  "<p>Location has been successfully updated.</p>";

return $dynamic;

}


/* Form for creating a new location */

function createLocation()
{
  sqlConnect();
	$query = "select name from mapimgs";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	
	$dynamic = "<form action=\"index.php\" method=\"get\">";
	$dynamic .=	"<fieldset><legend>Create New Location</legend>";
	$dynamic .= "<label for=\"begin\">Location Name:&nbsp;</label><input type=\"text\" size=\"25\" name=\"locname\" id=\"location\" value=\"\" /><br />";
	$dynamic .= "<label for=\"end\">Link:&nbsp;<input style=\"padding-left:17px\" type=\"text\" size=\"25\" name=\"link\" id=\"end\" value=\"\" /><br />";

          $dynamic .= "<label>Map:&nbsp;</label><select name=\"maps\">";
          $dynamic .= "<option value=\"None\" selected = \"selected\">None</option>";
          
          /*  Show a list of all the maps */

          while ($c = mysql_fetch_array($result))
            	{
            	$name = $c['name'];
              	  
              $dynamic .= "<option value=\"$name\">$name</option>";
              }
            
            	$dynamic .= "</select>";



  $dynamic .= "<input type=\"hidden\" size=\"25\" name=\"section\" id=\"hidden\" value=\"locations\" /><br />";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"mode\" id=\"hidden\" value=\"processcreate\" /><br />";
  $dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Add location\" />";
	$dynamic .= "</fieldset></form>";
	
	return $dynamic;
}

/* Update the database by inserting a new location into it */

function processCreateLocation()
{
sqlConnect();

/* Determine whether (and what) map has been assigned to the new location */

if($_GET['maps'] == "None"){
$is_map = 0;
$mapid = "NULL";
}
else{

$query = "select mapid from mapimgs where name = '".$_GET['maps']."'";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	$d = mysql_fetch_array($result);

$is_map = 1;
$mapid = $d['mapid'];

}

$locname = $_GET['locname'];
$link = $_GET['link'];

/* Update db */

$query = "INSERT INTO `maps` ( `location` , `text_link` , `is_mapfile`, `mapid` )
      VALUES ('$locname', '$link', ".$is_map.", ".$mapid.");";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());

// create a new table `stacks_locid`
$query = "SELECT location_id from maps WHERE location = '$locname'";
$result = mysql_query($query);
$loc_id = mysql_result($result, 0);

$query = "CREATE TABLE `stacks_$loc_id` (`beginning_call_number` varchar(50) , `ending_call_number` varchar(50) ,`range_number` int(50) , `std_beg` varchar(75) , `std_end` varchar(75) ,  `x_coord` smallint(4) , `y_coord` smallint(4) ,
  PRIMARY KEY(`range_number`)
)";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());


$dynamic = "<p>Location has been successfully created.</p>";

return $dynamic;
}


?>
