<?php
/*  index.php
    Last update: 6/04/07
    Last modified: Woon (woonkhang@gmail.com)
    Changes: fixed some bugs with the menu not displaying properly (may)
	move the select map drop down menu to the top left, and validates if user has select a map before performing some operations.
	the user must select a map everytime they revisit this page.

	Delete the section 'assign map coordinates' under section 'maps', and integrate it into the 'mapit' function under 'view/edit ranges'.
	now, you won't have to type in the range manually, just click on the coordinate where you want to assign. (June 07)
	Redesigned the whole call number searching algorithm and recode ../includes/standardize.php.
	Fixes some problems such as call no with decimals, and call no ending with long numbers not working properly. (June 04 07)

    index.php contains the main interface for the stack system administration.  The file prints the
    main menu, prints the content area, and calls action functions depending on the mode and section
    information that is passed to it.
*/

/*  Utility file containing the sqlConnect function and login information for database connections. */

include('../../includes/sqlConnect.php');

/*  The following five include files are all very similar -- they contain the functions for each
    main section of the stack administration.  Each section corresponds to a set of functions in
    one file. */

include("maps.php");
include("icons.php");
include("locations.php");
include("stacks.php");
include("utilities.php");

/*  ../includes/standardize.php contains the algorithm for conversion of the user-specified call number to a
    number more suitable for an alphabetical database range search. */

include("../../includes/standardize.php");

/*  Section is a broad categorization that can include maps, locations, icons, stacks, etc.
    This variable should always be defined for the purposes of the javascript menu knowing
    which section to display and which functions to run */

if (array_key_exists("section", $_GET))
	$section = $_GET['section'];
else
	$section = "";

/*  Mode is a more specific variable that determines the specific function to run within
    a section.  The mode can be thought of as an "action." */

if (array_key_exists("mode", $_GET))
	$mode = $_GET['mode'];
else
	$mode = "";

/*  Sometimes file information is passed to index.php, usually when a new map or icon
    is uploaded */

if ($_FILES){
	$fileinfo1 = $_FILES['uploadedfile']['name'];
	$fileinfo2 = $_FILES['uploadedfile']['tmp_name'];
	}
else
	$file = "";

/*  These two statements are also used when a new file is uploaded to the server. */

if (array_key_exists("iconname", $_POST))
	$iconname = $_POST['iconname'];
else
	$iconname = "";

if (array_key_exists("mapname", $_POST))
	$mapname = $_POST['mapname'];
else
	$mapname = "";

/*  Check whether an icon or map has been uploaded.  If so, fill $newname. */

if($iconname > $mapname){
$newname = $iconname;
}
elseif($mapname > $iconname){
$newname = $mapname;
}
else{
$newname = "";
}

/*  This is the main switch that determines program flow.  Depending on the section and mode
    a certain function is executed before eventually returning to this menu. */

switch ($section)
{
case "stacks":
	if($mode == "view"){
    $dynamic = stackList();
    }
  elseif($mode == "add"){
    $dynamic = addRange();
    }
  elseif($mode == "edit"){
    $dynamic = editRange();
    }
  elseif($mode == "delete"){
    $dynamic = deleteRange();
    }
  elseif($mode == "processaddrange"){
    $dynamic = processAddRange();
    }
  elseif($mode == "processdeleterange"){
    $dynamic = processDeleteRange();
    }
  elseif($mode == "processeditrange"){
    $dynamic = processEditRange();
    }
  else{
    $dynamic = "";
    }
	break;
case "maps":
  if($mode == "viewmaps"){
    $dynamic = mapList();
    }
  elseif($mode == "upload"){
    $dynamic = uploadMap();
    }
    elseif($mode == "processupload"){
    $dynamic = processUpload($fileinfo1, $fileinfo2, $section, $newname);
    }
/*  elseif($mode == "reassign"){
    $dynamic = assignCoords();
    }*/
  elseif($mode == "selectactive"){
    $dynamic = selectLocation();
    }
  elseif($mode == "processcurrentselect"){
    $dynamic = processSelect();
    }
  elseif($mode == "delete"){
    $dynamic = deleteMap();
    }
  elseif($mode == "processdelete"){
    $dynamic = processDelete($section);
    }
  else{
    $dynamic = "";
    }
	break;
case "icons":
  if($mode == "iconview"){
    $dynamic = iconList();
    }
  elseif($mode == "iconupload"){
    $dynamic = uploadIcon();
    }
  elseif($mode == "processupload"){
    $dynamic = processUpload($fileinfo1, $fileinfo2, $section, $newname);
    }
  elseif($mode == "iconassign"){
    $dynamic = assignIcon();
    }
  elseif($mode == "processassign"){
    $dynamic = processAssign($section);
    }
  elseif($mode == "delete"){
    $dynamic = deleteIcon();
    }
  elseif($mode == "processdelete"){
    $dynamic = processDelete($section);
    }
  else{
    $dynamic = "";
    }
	break;
case "utilities":
  if($mode == "printall"){
    $dynamic = printAllStacks();
    }
  elseif($mode == "printpartial"){
    $dynamic = printPartialStacks();
    }
  elseif($mode == "searchstacks"){
    $dynamic = searchStacks();
    }
  else{
    $dynamic = "";
    }
  break;
case "locations":
  if($mode == "viewlocations"){
    $dynamic = printLocations();
    }
  elseif($mode == "edit"){
    $dynamic = editLocation();
    }
  elseif($mode == "processedit"){
    $dynamic = processEditLocation();
    }
  elseif($mode == "delete"){
    $dynamic = deleteLocation();
    }
  elseif($mode == "processdelete"){
    $dynamic = processDelete($section);
    }
  elseif($mode == "createnew"){
    $dynamic = createLocation();
    }
  elseif($mode == "processcreate"){
    $dynamic = processCreateLocation();
    }
  else{
    $dynamic = "";
    }
  break;
default:
	$dynamic = instructions();
}


/*  After the switch determines what text to display, pass that text to the printPage function and
    print the page for the user. */

printPage($dynamic, $section, $mode);

/* Display error message when no map is selected from the dropdown menu */

function errorNoMapSelected() {
	$dynamicText = "<p>I have encountered error 001110101101.</p>";
	$dynamicText .= "<p>Please feed me a map first.</p>";
	$dynamicText .= "<p>You may select a location from the dropdown menu on your left.</p>";
	return $dynamicText;
}

/* Show the instructions to the user in the home page */
function instructions() {
	sqlConnect();
	$sql = mysql_query("UPDATE `current` SET `location_id` = \"\" WHERE `location_id` is not null LIMIT 1")
	or die("Invalid query: " . mysql_error());

	$dynamic = "<p>Welcome to the interactive stack map.</p>";
	$dynamic .= "<p>Please select a location from the dropdown menu on your left. Please ensure that you have the correct location selected as your \"current location.\"</p>";
	$dynamic .= "<p>After that, you may navigate through the menu by clicking on the section you want.</p>";
	return $dynamic;
}


/*  Utility function to get the location id for the "current location" in the control panel. */

function getCurrentLocationID(){
sqlConnect();
$sql = mysql_query("SELECT location_id FROM current");
if($row = mysql_fetch_array($sql))
  $current_location_id = $row['location_id'];
else
  $current_location_id ="";
return $current_location_id;
}


/*  When the user uploads a new map or icon, this function moves the file to the server and
    updates the database.  The function is used for both maps and icons. */

function processUpload($fileinfo1, $fileinfo2, $section, $newname){

$target_path = "../".$section."/";

/* Add the original filename to our target path.
Result is "uploads/filename.extension" */

$target_path = $target_path . basename( $fileinfo1);
$fileinfo2;

if(move_uploaded_file($fileinfo2, $target_path)) {
    $dynamic =  "<p>The file ".  basename( $fileinfo1).
    " has been uploaded.</p>";

    /* Update database */

    sqlConnect();

    if($section == "maps"){
      $query = "INSERT INTO `mapimgs` ( `mapid` , `name` , `filename` )
      VALUES ('', '$newname', '$fileinfo1');";
	    $result = mysql_query($query) or die("Invalid query: " . mysql_error());

	    $query = "SELECT mapid FROM mapimgs WHERE name = \"".$newname."\"";
	    $result = mysql_query($query) or die("Invalid query: " . mysql_error());
	    $d = mysql_fetch_array($result);
	    $newmapid = $d['mapid'];

	    $query = "INSERT INTO `iconassign` ( `mapid` )
      VALUES ('$newmapid');";
	    $result = mysql_query($query) or die("Invalid query: " . mysql_error());

/*      $query = "UPDATE `current` SET `mapid` = ".$newmapid." WHERE `mapid` is not null LIMIT 1";
      $result = mysql_query($query) or die("Invalid query: " . mysql_error());
*/
	    $dynamic .= "<P>".$newname." has been uploaded.  Please make sure you assign an icon to use as a marker on this map.  If you'd like to make changes to another map instead, please go to \"Select Active Map.\"</P>";
      }

    elseif($section == "icons"){
      $query = "INSERT INTO `iconimgs` ( `icoid` , `name` , `filename` )
      VALUES ('', '$newname', '$fileinfo1');";
	    $result = mysql_query($query) or die("Invalid query: " . mysql_error());
      }
      elseif($section == "locations"){
      $query = "INSERT INTO `maps` ( `icoid` , `name` , `filename` )
      VALUES ('', '$newname', '$fileinfo1');";
	    $result = mysql_query($query) or die("Invalid query: " . mysql_error());
      }
    }

/*  Note that this is the result of the move_uploaded_file() function returning false */

  else{
    $dynamic =  "<p>There was an error uploading the file, please try again!</p>";
}


return $dynamic;

}



/* Similar to processUpload, this function handles a file/record's delete on both the server
  and the database */

function processDelete($type){

/*  User could have clicked no on the confirmation page, hence do nothing but inform him/her
    that the delete failed. */

if($_POST['submit'] == "No")
{
$dynamic = "<p>Delete canceled.</p>";
}
else{
    /*  Usually there is a record number/name to delete, so $id is set equal to that data
        passed to this page. */

    $id = $_GET['i'];

    sqlConnect();

    if($type == "maps"){

    /*  Remove map from database */

    $query = "select filename FROM mapimgs where mapid = $id";
    $result = mysql_query($query) or die("Invalid query: " . mysql_error());
    $d = mysql_fetch_array($result);

    $query = "DELETE FROM `mapimgs` WHERE `mapid` = $id LIMIT 1";
    $result = mysql_query($query) or die("Invalid query: " . mysql_error());

    $query = "DELETE FROM `iconassign` WHERE `mapid` = $id LIMIT 1";
    $result = mysql_query($query) or die("Invalid query: " . mysql_error());

    /* Remove map from file server */

    $filename = $d['filename'];
    $path = "../maps/".$filename;
    unlink($path);
    $dynamic = "<p>Map has been successfully deleted.</p>";
    }
    elseif($type == "icons"){

    /* Remove icon from database */

    $query = "select filename FROM iconimgs where icoid = $id";
    $result = mysql_query($query) or die("Invalid query: " . mysql_error());
    $d = mysql_fetch_array($result);

    $query = "DELETE FROM `iconimgs` WHERE `icoid` = $id LIMIT 1";
    $result = mysql_query($query) or die("Invalid query: " . mysql_error());

    /* Remove icon from file server */

    $filename = $d['filename'];
    $path = "../icons/".$filename;
    unlink($path);
    $dynamic = "<p>Icon has been successfully deleted.</p>";
    }
    elseif($type == "locations"){

    /*  Locations only exist virtually (no file) and thus only need to be removed from the db.
        They also do not have their own table, so only a location record needs to be deleted. */

    $query = "DELETE FROM `maps` WHERE `location_id` = '".$id."' LIMIT 1";
    $result = mysql_query($query) or die("Invalid query: " . mysql_error());

    $query = "DROP TABLE `stacks_$id`";	// drop stacks table if exists
	mysql_query($query);

    $dynamic = "<p>Location has been successfully deleted.</p>";
    }
}

return $dynamic;

}


/*  This function contains the code and HTML to generate the page the user sees.  Most content-related
    data comes from the $dynamicText variable that is passed to it.  $section determines which menu to
    show collapsed */

function printPage($dynamicText, $section, $mode)
{
sqlConnect();


/*  These two database queries get the information for the current map so it can be displayed on every
    page. */

// $sql3 = mysql_query("SELECT mapid FROM current");
// $row3 = mysql_fetch_array($sql3);
// $mapid = $row3['mapid'];

// $sql3 = mysql_query("SELECT name FROM mapimgs WHERE mapid = ".$mapid);
// $row3 = mysql_fetch_array($sql3);
// $currentmapname = $row3['name'];

/*  Print header and style information */

print <<<END

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Stack Map Control Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<style type="text/css">
*{margin:0;padding:0;}
#container{width:100%;margin-left:auto;margin-right:auto;}
#menu{float:left;width:200px;background:none;font:0.8em/1.7 Tahoma, Verdana, sans-serif;margin-right:10px;}
#text{width:100%-200px;margin-left:200px;font-size:1.0em;background:#ffffff;}
p{padding-left:35px;font:0.9em/1.7 Tahoma, Verdana, sans-serif;text-align:center-200px;margin-left:auto;margin-right:auto;}

#header{width:100%;margin-left:auto;margin-right:auto;text-align:center}
#logo{height:9.1em;width:50em;}
#stackList { width:75%;margin-left:auto;margin-right:auto;text-align:center;font:0.9em/1.7 Tahoma, Verdana, sans-serif;border: solid black 1px }
th { background:#fee3ad;color:#000000;font-weight:normal; padding-left:5px;padding-right:5px;}

form { width:75%;margin-left:auto;margin-right:auto; }
form#currentMap {margin-left:8px;}
input {	color:#781351;background:#fee3ad;border:1px solid #781351;text-align:left; }
fieldset { border: 1px solid #781351;width: 100%;height:125px; }
legend { font:0.9em/1.7 Tahoma, Verdana, sans-serif;color: #000000;background: #ffa20c;border: 1px solid #781351;padding: 2px 6px }
label { font:0.9em/1.7 Tahoma, Verdana, sans-serif; padding-left:20px; width: 150px;}
#submit { position:relative; left: +20px; color: #000000;background: #ffa20c;border:1px solid #781351;text-align:left; }

a{display:block;text-decoration:none;width:100%;line-height:30px;}
ul{list-style-type:none;text-align:center;}
li{display:inline;}

#main li a:link,ul#main li a:visited {background:#ffa20c;color:#000000;border-top:2px solid #fff;}
#main li a:link:hover,ul#main li a:visited:hover {background:#011c52;color:#ffffff}

ul#main li ul.sub li a:link,ul#main ul.sub li a:visited {border-top:2px solid #fff;background:#fee3ad;color:#000000;}
ul#main li ul.sub li a:link:hover,ul#main ul.sub li a:visited:hover {background:#005df3;color:#ffffff;}

END;

  /* Depending on the section, expand a given menu section */

	if ($section == "stacks")
	{
		echo "#ul_one {display:block;}";
		echo "#ul_two {display:none;}";
		echo "#ul_three {display:none;}";
		echo "#ul_five {display:none;}";
		echo "#ul_four {display:none;}";
	}
	else if ($section == "maps" && $mode != "processcurrentselect")
	{
		echo "#ul_one {display:none;}";
		echo "#ul_two {display:block;}";
		echo "#ul_three {display:none;}";
		echo "#ul_five {display:none;}";
		echo "#ul_four {display:none;}";

	}
	else if ($section == "icons")
	{
		echo "#ul_one {display:none;}";
		echo "#ul_two {display:none;}";
		echo "#ul_three {display:block;}";
		echo "#ul_five {display:none;}";
		echo "#ul_four {display:none;}";
	}
	else if ($section == "locations")
	{
		echo "#ul_one {display:none;}";
		echo "#ul_two {display:none;}";
		echo "#ul_three {display:none;}";
		echo "#ul_five {display:block;}";
		echo "#ul_four {display:none;}";
	}
	else if ($section == "utilities")
	{
		echo "#ul_one {display:none;}";
		echo "#ul_two {display:none;}";
		echo "#ul_three {display:none;}";
		echo "#ul_five {display:none;}";
		echo "#ul_four {display:block;}";
	}
	else
		echo "#ul_one, #ul_two, #ul_three, #ul_five, #ul_four {display:none;}";

	$current_location = selectLocation();

/* Print the menu */

print <<<END

</style>
<script type="text/javascript">

function changeOpenmenu(id) {
	var all_uls = new Array();
	all_uls[0]='ul_one';
	all_uls[1]='ul_two';
	all_uls[2]='ul_three';
	all_uls[3]='ul_five';
	all_uls[4]='ul_four';

	for(i=0; i<all_uls.length; i++) {
		document.getElementById(all_uls[i]).style.display = 'none';
	}
	the_ul = document.getElementById(id);
	the_ul.style.display = 'block';
//	clearButtons();
}

function clearButtons() {
	if (!document.getElementsByTagName) return;	// what does this mean? shouldn't a parameter tagname be passed to the function getElementsByTagName?
	var anchors = document.getElementsByTagName("a");
	for (var i=0; i<anchors.length; i++) {
		var anchor = anchors[i];
		anchor.blur();
	}
}
</script>
</head>
<body>

<div id="header">
	<a href="index.php" alt="Control Panel: Home"><img src="header.jpg" border="0" alt="Header graphic." id="logo"></a>
</div>

<div id="container">
	<div id="menu">
		Current Location:<br /> $current_location
		<ul id="main">

		<li id="li_one"><a href="javascript:changeOpenmenu('ul_one');">Manage Stacks</a>
		<ul class="sub" id="ul_one">
		<li><a href="index.php?section=stacks&mode=view">View/Edit Ranges</a></li>
		<li><a href="index.php?section=stacks&mode=add">Add a Range</a></li>
		</ul>
		</li>

		<li id="li_two"><a href="javascript:changeOpenmenu('ul_two');">Manage Maps</a>
		<ul class="sub" id="ul_two">
		<li><a href="index.php?section=maps&mode=viewmaps">View Maps</a></li>
		<li><a href="index.php?section=maps&mode=upload">Upload New Map</a></li>
		<!--<li><a href="index.php?section=maps&mode=reassign">Assign Map Coordinates</a></li>-->
		<!--<li><a href="index.php?section=maps&mode=locations">Map locations</a></li>-->
		<!--<li><a href="index.php?section=maps&mode=selectactive">Select Active Map</a></li>-->
		</ul>
		</li>

		<li id="li_three"><a href="javascript:changeOpenmenu('ul_three');">Manage Icons</a>
		<ul class="sub" id="ul_three">
		<li><a href="index.php?section=icons&mode=iconview">View Icons</a></li>
		<li><a href="index.php?section=icons&mode=iconupload">Upload New Icon</a></li>
		<li><a href="index.php?section=icons&mode=iconassign">Assign Icons</a></li>
		</ul>
		</li>

		<li id="li_five"><a href="javascript:changeOpenmenu('ul_five');">Manage Locations</a>
		<ul class="sub" id="ul_five">
		<li><a href="index.php?section=locations&mode=viewlocations">View/Edit Locations</a></li>
		<li><a href="index.php?section=locations&mode=createnew">Create New Location</a></li>
		<!--<li><a href="index.php?section=locations&mode=iconassign">Assign Icons</a></li>-->
		</ul>
		</li>

		<li id="li_four"><a href="javascript:changeOpenmenu('ul_four');">Utilities</a>
		<ul class="sub" id="ul_four">
		<li><a href="index.php?section=utilities&mode=printall">Print All Stacks</a></li>
		<li><a href="index.php?section=utilities&mode=printpartial">Print Partial Chart</a></li>
		<li><a href="index.php?section=utilities&mode=searchstacks">Test Stack Map</a></li>
		</ul>
		</li>

		</ul>
	</div>

	<div id="text">
	$dynamicText
	</div>

</div>
</body>
</html>

END;

/* End of menu */

}
?>
