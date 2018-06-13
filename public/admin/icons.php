<?php
/*  icons.php
    Last update: 4/16/07
    
    This file has functions included for the purposes of the index.php Stack Map Admin control panel.
    icons.php has a number of functions called by index.php's main menu that relate to printing and 
    managing the icons that are available to use as markers on a map.
*/


/*    Creates the table of icons and displays them for viewing and deletion purposes */

function iconList()
{
	sqlConnect();

  /*  Find all icons */
	
	$query = "select * from iconimgs";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());
	
	/*  Begin table */
	
	$dynamicText = "<table cellpadding=\"0\" cellspacing=\"0\" id=\"stackList\"><tr><th>Icon name</th><th>File name</th><th>File size</th><th>View</th><th colspan=\"2\">&nbsp;</th></tr>";

  /*  $number keeps track whether a record is odd or even -- used for shading purposes on the table */

  $number = 1;
	
  /*  For each icon that has been uploaded... */

	while ($d = mysql_fetch_array($result))
	{
	
	  /* Set icon's name, filename, and file size */ 
	
		$name = $d['name'];
		$filename = $d['filename'];
		$icoid = $d['icoid'];

    $size = (filesize("../icons/".$filename))/1000;

		if ( ($number % 2) == 1 )
			$color = "ffffff";
		else
			$color = "fffaef";
	
    /*  Print one row */		
			
		$dynamicText .= "<tr style=\"background:#$color;\"><td>$name</td><td>$filename</td><td>$size K</td><td><img src=\"../icons/$filename\"></td><td><a href=\"index.php?section=icons&mode=delete&i=$icoid\"><img border=\"0\" src=\"delete.png\"></a></td></tr>";
    $number++;
	}
	
	$dynamicText .= "</table>";
	
	return $dynamicText;
}


/* Creates a form for uploading a new icon file */

function uploadIcon()
{


  $dynamic = "<form enctype=\"multipart/form-data\" action=\"index.php?section=icons&mode=processupload\" method=\"post\">";
	$dynamic .=	"<fieldset><legend>Upload icon</legend>";
	$dynamic .= "<input type=\"hidden\" size=\"25\" name=\"MAX_FILE_SIZE\" id=\"hidden\" value=\"1000000\" /><br />";
	$dynamic .= "<label for=\"upload\">File:&nbsp;</label><input type=\"file\" size=\"25\" name=\"uploadedfile\" id=\"upload\" value=\"\" /><br />";
  $dynamic .= "<label for=\"iconname\">Icon name:&nbsp;</label><input type=\"text\" size=\"25\" name=\"iconname\" id=\"iconname\" value=\"\" /><br />";
  $dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Upload file\" />";
	$dynamic .= "</fieldset></form>";
  return $dynamic;
}


/* Prompts user yes/no regarding an icon deletion */

function deleteIcon()
{
	  
  $index = $_GET['i'];
		
	$dynamic = "<form action=\"index.php?section=icons&mode=processdelete&i=$index\" method=\"post\">";
	$dynamic .=	"<fieldset><legend>Confirm delete</legend>";
  $dynamic .= "<label for=\"begin\">Are you sure you want to delete this icon?&nbsp;</label><br /><br />";
	$dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Yes\" />";
	$dynamic .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"No\" />";
	$dynamic .= "</fieldset></form>";
	
	return $dynamic;
}


/*  Prints out a list of all maps with a select (dropdown) box corresponding to each.
    each box has all the icons and a "select" button to associate a new icon with one
    of the maps. */

function assignIcon()
{

	
sqlConnect();	
$query = "select * from iconimgs";
	$result = mysql_query($query) or die("Invalid query: " . mysql_error());

$query2 = "select * from mapimgs";
	$result2 = mysql_query($query2) or die("Invalid query: " . mysql_error());

$dynamicText = "";

/* For each map that exists... */

while ($c = mysql_fetch_array($result2)){	

 
 /*  Find that map's currently assigned icon */

  $query3 = "select iconid from iconassign where mapid = ".$c['mapid']."";
  $result3 = mysql_query($query3) or die("Invalid query: " . mysql_error());
  $e = mysql_fetch_array($result3);
  
   
 /* ... print the map's name and a dropdown box. */
 
  $dynamicText .= "<P>".$c['name']."</P>";

  $dynamicText .= "<form enctype=\"multipart/form-data\" action=\"index.php\" method=\"get\">";
	$dynamicText .= "<select name=\"icons\">";


  /* For each icon that exists... add a dropdown box item. */
  
  while ($d = mysql_fetch_array($result))
	{
      $name = $d['name'];
	   /* Does this icon match the currently assigned one? */
	 if($d['icoid'] == $e['iconid']){
      $selected = "selected = \"selected\"";
      $dynamicText .= "<option value=\"$name\" $selected>$name</option>";  
      }
    else{
       $selected = "";
       $dynamicText .= "<option value=\"$name\">$name</option>";
       }
	
	
	   
     //$dynamicText .= "<option value=\"$name\">$name</option>";
  }

	$dynamicText .= "</select>";
	$dynamicText .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Assign\" />";
	$dynamicText .= "<input type=\"hidden\" size=\"25\" name=\"section\" id=\"hidden\" value=\"icons\" /><br />";
	$dynamicText .= "<input type=\"hidden\" size=\"25\" name=\"mode\" id=\"hidden\" value=\"processassign\" /><br />";
	$dynamicText .= "<input type=\"hidden\" size=\"25\" name=\"map\" id=\"hidden\" value=\"".$c['name']."\" /><br />";

  $dynamicText .= "</form>";
	
$dynamicText .= "<br />";

$result = mysql_query($query) or die("Invalid query: " . mysql_error());
}	
	
	
	return $dynamicText;
}


/* Update the database with a new icon-to-map assignment and give the user feedback. */

function processAssign(){

sqlConnect();

$sql = mysql_query("SELECT mapid FROM mapimgs WHERE name = \"".$_GET["map"]."\"");
if($row = mysql_fetch_array($sql))
  $mapid = $row['mapid'];
else
  $mapid = "";

$sql = mysql_query("SELECT icoid FROM iconimgs WHERE name = \"".$_GET["icons"]."\"");
if($row = mysql_fetch_array($sql))
  $iconid = $row['icoid'];
else
  $iconid = "";

$sql = mysql_query("UPDATE `iconassign` SET `iconid` = ".$iconid." WHERE `mapid` = ".$mapid." LIMIT 1") 
or die("Invalid query: " . mysql_error());


$dynamic = "<p>".$_GET["icons"]." has been successfully assigned to ".$_GET["map"].". </p><br />";

return $dynamic;

}

?>
