<?php
/*  stacksearch.php
    Last update: 4/23/07

    The purpose of this utility is to print a map for the user detailing where he/she can find a
    library resource with a call number matching the number from the user's search query.
    stacksearch.php is called by a form in the main index.html (name subject to change) search page.
    The data from the form is sent via GET to this PHP page and the table 'stacks' (in database rangeguide)
    is searched for the appropriate call number range.  The stack area for that range is determined and a
    marker is placed on the stack map in the appropriate location.
*/

/*  Utility file containing the sqlConnect function and login information for database connections. */

include('../includes/sqlConnect.php');

/*  ../includes/standardize.php contains the algorithm for conversion of the user-specified call number to a
    number more suitable for an alphabetical database range search. */

include("../includes/standardize.php");

/*  This functionality may eventually be removed -- for now it's there to reassure the user that the
    correct number was queried. */

/* kloor 2015-02-09
echo "You selected call number ";
echo $_GET['callnumber']."<br />\n";
*/
echo "<br />\n";
echo "&nbsp;&nbsp;Your selected call number ";
echo $_GET['callnumber'];

$callnum = $_GET['callnumber'];

/*  Update call number formatting (see above) */
$callnum = standardize($callnum);

/* Connect to the 'rangeguide' database  */
$con = sqlConnect();

/*  Came from a page that will require a selection */

/*if (strpos($_SERVER['HTTP_REFERER'], "search.php")){*/

if (array_key_exists("location_id", $_GET)){
$location_id = $_GET['location_id'];
}

else{
	$sql = mysql_query("SELECT location_id FROM current");
	if($row = mysql_fetch_array($sql))
	{
		$location_id = $row['location_id'];
	}
else
	$location_id ="";
}

$result = mysql_query("SELECT mapid FROM maps WHERE location_id = ".$location_id);
$mapid = mysql_result($result, 0);

/* The search takes advantage of the alphabetical nature of call numbers to find the
appropriate number within the range */

$sql = mysql_query("SELECT * FROM stacks_$location_id WHERE
std_beg <= \"$callnum\" AND std_end >= \"$callnum\"");

/*  This second query is necessary now that the user can assign icons dynamically through
    the admin interface.  It finds out which icon is assigned to the current map and gets
    the file name.  Notice that mapid is set to the constant 1 presently -- that number
    can eventually be changed to a variable equal to the map id of whatever the "current
    map" is. */

/* CHANGE ABOVE DOCUMENTATION */


$sql3 = mysql_query("SELECT filename FROM mapimgs WHERE mapid = ".$mapid);
if($row3 = mysql_fetch_array($sql3))
  $mapfile = $row3['filename'];
else
  $mapfile ="";

$sql2 = mysql_query("SELECT filename FROM iconassign, iconimgs WHERE iconid = icoid AND mapid = ".$mapid);
if($row2 = mysql_fetch_array($sql2))
  $iconfile = $row2['filename'];
else
  $iconfile ="";

$sql4 = mysql_query("SELECT location FROM maps WHERE location_id = ".$location_id);
if($row4 = mysql_fetch_array($sql4))
  $location = $row4['location'];
else
  $location ="";

/*  Iterate through the resultset and find the range number.  Display it to the user.
    Although this works by looping through the array, it should _never_ return more than one result. */

while($row = mysql_fetch_array($sql))
  {
  /*if($row['range_number']== 999){
    echo "Please go to the Science Library in the Ogg Science Building for any books range ".$row['beginning_call_number'] . "--" . $row['ending_call_number']. "" ;
    echo "<br />\n";
    $index = $row['range_number'];
    echo "<br />\n";
    }
  else{*/
    /* kloor 2015-02-09
    echo "Found in <b>range " . $row['range_number']." </b> of ".$location." (".$row['beginning_call_number'] . "-- " . $row['ending_call_number']. ")" ;
    */
    echo " was found in <b>range " . $row['range_number'] . "</b> of " . $location . ".";
    echo "<br />\n";
    $index = $row['range_number'];
    /* kloor 2015-02-09
    echo "Placing a marker <img src=\"./icons/$iconfile\" alt=\"Map marker\" /> in section ".$index."... <br /><br />\n";
    */
    echo "&nbsp;&nbsp;Placing a marker <img src=\"./icons/$iconfile\" alt=\"Map marker\" style=\"vertical-align:middle\" /> for range ".$index."... <br />\n";
    //}
  }

/*  Query the database again to find the x and y coordinates associated with the stack section that
    the previous search returned.  */

if(isset($index)){

  /*  Book is actually in the Science Library across campus -- don't display a Jerome map */

  if($index == 999){
    // For now do nothing... in the future we may display a map to the Ogg library
    mysql_close($con);
    }
  else{
    /* stacksearch.php has a valid stack number in range and the marker can be placed */

    $xycoords = mysql_query("SELECT x_coord, y_coord FROM stacks_$location_id WHERE range_number = \"$index\"");

    while($row = mysql_fetch_array($xycoords)){
      $img_x_coord = $row['x_coord'];
      $img_y_coord = $row['y_coord'];
      }

    /*  This 76 pixel offest is used to move the marker down from its original mapping.  The (x,y) coordinates
        stored in the database were relative to the image only, not the image plus the text.  This might need
        to be changed if the content above the image is modified. */

    $img_y_coord -= 10;
	$img_x_coord -= 10;


    /*  HTML for the popup window (in which this code executes).  In order:
        1.  Use the local file stackmap2006.gif as the background to the popup box
        2.  Place the star at the x,y offset specified above */


/*  Special code block for the print-only style.  The page was having problems placing
    The stars in the same location in the print preview as when it rendered on the screen.
    Consequently, there needs to be a different x,y offset when the map is printed.  These
    two blocks of code define which of the two offsets should be used. */

PRINT<<<END
<style media="print">
END;
echo  ".noscreen { left:".$img_x_coord."px; top:".($img_y_coord)."px; position:absolute; }";
echo  ".noprint { display:none; }";
PRINT<<<END
  </style>
END;

PRINT<<<END
<style media="screen">
END;
echo  ".noprint { position:absolute; top:".$img_y_coord."px; left:".$img_x_coord."px; }";
echo  ".noscreen { display:none; }";
PRINT<<<END
  </style>
END;

    /*  End styling, begin drawing map */

    echo "<div style=\"position:absolute\"> <img src=\"./maps/$mapfile\" alt=\"Library stack map\" style=\"\" />\n";

    /* Star placement for the computer screen */

    echo "<img class='noprint' width=\"20px\" height=\"20px\" src=\"./icons/$iconfile\" alt=\"Map marker\" />";

    /* Star placement for printer and print preview */

    echo "<img class='noscreen' src=\"./icons/$iconfile\" alt=\"Map marker\" />";
    echo "</div>\n";
    }
  }
  else{
    /*  $index was never set, meaning the call number wasn't found in the db */
    echo "Invalid call number.  Please try again...<br />";
    }
PRINT<<<END

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try{
var pageTracker = _gat._getTracker("UA-3319349-2");
pageTracker._trackPageview();
} catch(err) {}</script>


END;
?>
