<?php
/*  index.php

    Contains the main interface for the stack system administration. The file
    prints the main menu, prints the content area, and calls action functions
    depending on the mode and section information that is passed to it.
*/

// Utility file containing the sqlConnect function and login information for
// database connections.
include('../../includes/sqlConnect.php');

// The following five include files are all very similar -- they contain the
// functions for each main section of the stack administration. Each section
// corresponds to a set of functions in one file.
include('../../includes/admin/maps.php');
include('../../includes/admin/icons.php');
include('../../includes/admin/locations.php');
include('../../includes/admin/stacks.php');
include('../../includes/admin/utilities.php');

// Contains the algorithm for conversion of the user-specified call number to a
// number more suitable for an alphabetical database range search.
include('../../includes/standardize.php');

// Section is a broad categorization that can include maps, locations, icons,
// stacks, etc. This variable should always be defined for the purposes of the
// javascript menu knowing which section to display and which functions to run
$section = '';

if (array_key_exists('section', $_GET)) {
    $section = $_GET['section'];
}

// Mode is a more specific variable that determines the specific function to
// run within a section. The mode can be thought of as an "action."
$mode = '';

if (array_key_exists('mode', $_GET)) {
    $mode = $_GET['mode'];
}

// Sometimes file information is passed to index.php, usually when a new map
// or icon is uploaded
$fileinfo1 = '';
$fileinfo2 = '';

if ($_FILES) {
    $fileinfo1 = $_FILES['uploadedfile']['name'];
    $fileinfo2 = $_FILES['uploadedfile']['tmp_name'];
}

// These two statements are also used when a new file is uploaded to the server.
$iconname = '';

if (array_key_exists('iconname', $_POST)) {
    $iconname = $_POST['iconname'];
}

$mapname = '';

if (array_key_exists('mapname', $_POST)) {
    $mapname = $_POST['mapname'];
}

// Check whether an icon or map has been uploaded. If so, fill $newname.
if ($iconname > $mapname) {
    $newname = $iconname;
} elseif ($mapname > $iconname) {
    $newname = $mapname;
} else {
    $newname = '';
}

// This is the main switch that determines program flow. Depending on the
// section and mode a certain function is executed before eventually returning
// to this menu.
switch ($section) {
    case 'stacks':
        if ($mode == 'view') {
            $dynamic = stackList();
        } elseif ($mode == 'add') {
            $dynamic = addRange();
        } elseif ($mode == 'edit') {
            $dynamic = editRange();
        } elseif ($mode == 'delete') {
            $dynamic = deleteRange();
        } elseif ($mode == 'processaddrange') {
            $dynamic = processAddRange();
        } elseif ($mode == 'processdeleterange') {
            $dynamic = processDeleteRange();
        } elseif ($mode == 'processeditrange') {
            $dynamic = processEditRange();
        } else {
            $dynamic = '';
        }

        break;

    case 'maps':
        if ($mode == 'viewmaps') {
            $dynamic = mapList();
        } elseif ($mode == 'upload') {
            $dynamic = uploadMap();
        } elseif ($mode == 'processupload') {
            $dynamic = processUpload(
                $fileinfo1,
                $fileinfo2,
                $section,
                $newname
            );
        } elseif ($mode == 'processcurrentselect') {
            $dynamic = processSelect();
        } elseif ($mode == 'delete') {
            $dynamic = deleteMap();
        } elseif ($mode == 'processdelete') {
            $dynamic = processDelete($section);
        } else {
            $dynamic = '';
        }

        break;

    case 'icons':
        if ($mode == 'iconview') {
            $dynamic = iconList();
        } elseif ($mode == 'iconupload') {
            $dynamic = uploadIcon();
        } elseif ($mode == 'processupload') {
            $dynamic = processUpload(
                $fileinfo1,
                $fileinfo2,
                $section,
                $newname
            );
        } elseif ($mode == 'iconassign') {
            $dynamic = assignIcon();
        } elseif ($mode == 'processassign') {
            $dynamic = processAssign($section);
        } elseif ($mode == 'delete') {
            $dynamic = deleteIcon();
        } elseif ($mode == 'processdelete') {
            $dynamic = processDelete($section);
        } else {
            $dynamic = '';
        }

        break;

    case 'utilities':
        if ($mode == 'printall') {
            $dynamic = printAllStacks();
        } elseif ($mode == 'printpartial') {
            $dynamic = printPartialStacks();
        } elseif ($mode == 'searchstacks') {
            $dynamic = searchStacks();
        } elseif ($mode == 'teststandard') {
            $dynamic = testStandard();
        } else {
            $dynamic = '';
        }

        break;

    case 'locations':
        if ($mode == 'viewlocations') {
            $dynamic = printLocations();
        } elseif ($mode == 'edit') {
            $dynamic = editLocation();
        } elseif ($mode == 'processedit') {
            $dynamic = processEditLocation();
        } elseif ($mode == 'delete') {
            $dynamic = deleteLocation();
        } elseif ($mode == 'processdelete') {
            $dynamic = processDelete($section);
        } elseif ($mode == 'createnew') {
            $dynamic = createLocation();
        } elseif ($mode == 'processcreate') {
            $dynamic = processCreateLocation();
        } else {
            $dynamic = '';
        }

        break;

    default:
        $dynamic = instructions();
}

// After the switch determines what text to display, pass that text to the
// printPage function and print the page for the user.
printPage($dynamic, $section, $mode);

// Display error message when no map is selected from the dropdown menu
function errorNoMapSelected()
{
    $dynamic = '<p>I have encountered error 001110101101.</p>';
    $dynamic .= '<p>Please feed me a map first.</p>';
    $dynamic .= '<p>You may select a location from the dropdown menu';
    $dynamic .= ' on your left.</p>';

    return $dynamic;
}

// Show the instructions to the user in the home page
function instructions()
{
    sqlConnect();

    mysql_query('DELETE FROM `current`')
    or die('Invalid query: ' . mysql_error());

    mysql_query('INSERT INTO `current` (`location_id`) VALUES("")')
    or die('Invalid query: ' . mysql_error());

    $dynamic = '<p>Welcome to the interactive stack map.</p>';
    $dynamic .= '<p>Please select a location from the dropdown menu on your';
    $dynamic .= ' left. Please ensure that you have the correct location';
    $dynamic .= ' selected as your "current location."</p>';
    $dynamic .= '<p>After that, you may navigate through the menu by clicking';
    $dynamic .= ' on the section you want.</p>';
}

// Utility function to get the location id for the "current location" in the
// control panel.
function getCurrentLocationID()
{
    sqlConnect();

    $sql = mysql_query('SELECT location_id FROM current')
    or die('Invalid query: ' . mysql_error());

    if ($row = mysql_fetch_array($sql)) {
        return $row['location_id'];
    }

    return '';
}

// When the user uploads a new map or icon, this function moves the file to the
// server and updates the database.
// Note: The function is used for both maps and icons.
function processUpload($fileinfo1, $fileinfo2, $section, $newname)
{
    $target_path = '../' . $section . '/';

    // Add the original filename to our target path.
    // Result is "uploads/filename.extension"

    $target_path = $target_path . basename($fileinfo1);

    if (move_uploaded_file($fileinfo2, $target_path)) {
        $dynamic = '<p>The file ' . htmlspecialchars(basename($fileinfo1));
        $dynamic .= ' has been uploaded.</p>';

        // Update database
        sqlConnect();

        if ($section == 'maps') {
            $query = sprintf(
                'INSERT INTO `mapimgs` (`mapid`, `name`, `filename`)' .
                ' VALUES ("", "%s", "%s")',
                mysql_real_escape_string($newname),
                mysql_real_escape_string($fileinfo1)
            );

            mysql_query($query)
            or die('Invalid query: ' . mysql_error());

            $query = sprintf(
                'SELECT mapid FROM mapimgs WHERE name = "%s"',
                mysql_real_escape_string($newname)
            );

            $result = mysql_query($query)
            or die('Invalid query: ' . mysql_error());

            $d = mysql_fetch_array($result);
            $newmapid = $d['mapid'];

            $query = sprintf(
                'INSERT INTO `iconassign` (`mapid`) VALUES ("%s")',
                mysql_real_escape_string($newmapid)
            );

            mysql_query($query)
            or die("Invalid query: " . mysql_error());

            $dynamic .= '<p>' . htmlspecialchars($newname);
            $dynamic .= ' has been uploaded. Please make sure you assign an';
            $dynamic .= ' icon to use as a marker on this map. If you\'d like';
            $dynamic .= ' to make changes to another map instead, please go';
            $dynamic .= ' to "Select Active Map."</p>';
        } elseif ($section == 'icons') {
            $query = sprintf(
                'INSERT INTO `iconimgs` (`icoid`, `name`, `filename`)' .
                ' VALUES ("", "%s", "%s")',
                mysql_real_escape_string($newname),
                mysql_real_escape_string($fileinfo1)
            );

            mysql_query($query)
            or die('Invalid query: ' . mysql_error());
        }
    } else {
        // Note that this is the result of the move_uploaded_file() function
        // returning false
        $dynamic = '<p>There was an error uploading the file,';
        $dynamic .= ' please try again!</p>';
    }

    return $dynamic;
}

// Similar to processUpload, this function handles a file/record's delete on
// both the server and the database
function processDelete($type)
{
    // User could have clicked no on the confirmation page, hence do nothing
    // but inform him/her that the delete failed.
    if (empty($_POST['submit']) || $_POST['submit'] == 'No') {
        $dynamic = '<p>Delete canceled.</p>';
    } elseif (isset($_GET['i'])) {
        // Usually there is a record number/name to delete, so $id is set equal
        // to that data passed to this page.
        $id = $_GET['i'];

        sqlConnect();

        if ($type == 'maps') {
            // Remove map from database
            $query = sprintf(
                'select filename FROM mapimgs where mapid = %s',
                mysql_real_escape_string($id)
            );

            $result = mysql_query($query)
            or die('Invalid query: ' . mysql_error());

            $d = mysql_fetch_array($result);

            $query = sprintf(
                'DELETE FROM `mapimgs` WHERE `mapid` = %s LIMIT 1',
                mysql_real_escape_string($id)
            );

            mysql_query($query)
            or die('Invalid query: ' . mysql_error());

            $query = sprintf(
                'DELETE FROM `iconassign` WHERE `mapid` = %s LIMIT 1',
                mysql_real_escape_string($id)
            );

            mysql_query($query)
            or die('Invalid query: ' . mysql_error());

            // Remove map from file server
            $filename = $d['filename'];
            $path = '../maps/' . $filename;
            unlink($path);

            $dynamic = '<p>Map has been successfully deleted.</p>';
        } elseif ($type == 'icons') {
            // Remove icon from database
            $query = sprintf(
                'select filename FROM iconimgs where icoid = %s',
                mysql_real_escape_string($id)
            );

            $result = mysql_query($query)
            or die('Invalid query: ' . mysql_error());

            $d = mysql_fetch_array($result);

            $query = sprintf(
                'DELETE FROM `iconimgs` WHERE `icoid` = %s LIMIT 1',
                mysql_real_escape_string($id)
            );

            mysql_query($query)
            or die('Invalid query: ' . mysql_error());

            // Remove icon from file server
            $filename = $d['filename'];
            $path = '../icons/' . $filename;
            unlink($path);

            $dynamic = '<p>Icon has been successfully deleted.</p>';
        } elseif ($type == 'locations') {
            // Locations only exist virtually (no file) and thus only need to
            // be removed from the db. They also do not have their own table,
            // so only a location record needs to be deleted.
            $query = sprintf(
                'DELETE FROM `maps` WHERE `location_id` = "%s" LIMIT 1',
                mysql_real_escape_string($id)
            );

            mysql_query($query)
            or die('Invalid query: ' . mysql_error());

            // drop stacks table if exists
            $query = sprintf(
                'DROP TABLE `stacks_%s`',
                mysql_real_escape_string($id)
            );

            mysql_query($query)
            or die('Invalid query: ' . mysql_error());

            $dynamic = '<p>Location has been successfully deleted.</p>';
        }
    }

    return $dynamic;
}

// This function contains the code and HTML to generate the page the user sees.
// Most content-related data comes from the $dynamicText variable that is
// passed to it. $section determines which menu to show collapsed
function printPage($dynamicText, $section, $mode)
{
    sqlConnect();

    // Print header and style information
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

    // Depending on the section, expand a given menu section
    if ($section == 'stacks') {
        echo '#ul_one {display:block;}';
        echo '#ul_two {display:none;}';
        echo '#ul_three {display:none;}';
        echo '#ul_five {display:none;}';
        echo '#ul_four {display:none;}';
    } elseif ($section == 'maps' && $mode != 'processcurrentselect') {
        echo '#ul_one {display:none;}';
        echo '#ul_two {display:block;}';
        echo '#ul_three {display:none;}';
        echo '#ul_five {display:none;}';
        echo '#ul_four {display:none;}';
    } elseif ($section == 'icons') {
        echo '#ul_one {display:none;}';
        echo '#ul_two {display:none;}';
        echo '#ul_three {display:block;}';
        echo '#ul_five {display:none;}';
        echo '#ul_four {display:none;}';
    } elseif ($section == 'locations') {
        echo '#ul_one {display:none;}';
        echo '#ul_two {display:none;}';
        echo '#ul_three {display:none;}';
        echo '#ul_five {display:block;}';
        echo '#ul_four {display:none;}';
    } elseif ($section == 'utilities') {
        echo '#ul_one {display:none;}';
        echo '#ul_two {display:none;}';
        echo '#ul_three {display:none;}';
        echo '#ul_five {display:none;}';
        echo '#ul_four {display:block;}';
    } else {
        echo '#ul_one, #ul_two, #ul_three, #ul_five, #ul_four {display:none;}';
    }

    $current_location = selectLocation();

    // Print the menu
    print <<<END
</style>
<script type="text/javascript">
function changeOpenmenu(id) {
    var all_uls = new Array();
    all_uls[0] = 'ul_one';
    all_uls[1] = 'ul_two';
    all_uls[2] = 'ul_three';
    all_uls[3] = 'ul_five';
    all_uls[4] = 'ul_four';

    for (i = 0; i < all_uls.length; i++) {
        document.getElementById(all_uls[i]).style.display = 'none';
    }

    the_ul = document.getElementById(id);
    the_ul.style.display = 'block';
}
</script>
</head>
<body>

<div id="header">
    <a href="index.php"><img src="header.jpg" border="0" alt="Interactive Stack Map" id="logo"></a>
</div>

<div id="container">
    <div id="menu">
    Current Location:<br />
    $current_location

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
    </ul>
    </li>

    <li id="li_four"><a href="javascript:changeOpenmenu('ul_four');">Utilities</a>
    <ul class="sub" id="ul_four">
    <li><a href="index.php?section=utilities&mode=printall">Print All Stacks</a></li>
    <li><a href="index.php?section=utilities&mode=printpartial">Print Partial Chart</a></li>
    <li><a href="index.php?section=utilities&mode=searchstacks">Test Stack Map</a></li>
    <li><a href="index.php?section=utilities&mode=teststandard">Test Standardization</a></li>
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

    // End of menu
}
