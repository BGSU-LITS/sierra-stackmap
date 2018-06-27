<?php
/*  x.php

    The purpose of this file is to process the information from the user's
    mapping input. The database is updated, then the results are queried and
    printed to
*/

include('../../includes/sqlConnect.php');
include '../../includes/standardize.php';

$xCoord = '';

if (isset($_POST['x'])) {
    $xCoord = $_POST['x'];
}

$yCoord = '';

if (isset($_POST['y'])) {
    $yCoord = $_POST['y'];
}

sqlConnect();

if (isset($_POST['location_id'])) {
    $set_mode = 'location';
    $location_id = $_POST['location_id'];

    // We use range number 0 as the placeholder to store the x-y position of
    // the location in this map
    $stackNo = 0;

    // check the total ranges (must be either one or zero)
    $result = mysql_query(sprintf(
        'SELECT count(*) as total_ranges from stacks_%s',
        mysql_real_escape_string($location_id)
    ));

    $total_ranges = mysql_result($result, 0);

    if ($total_ranges == 0) {
        // insert a new row in stacks_locid, so that later it can be updated
        // with the new map coordinate values.
        $result = mysql_query(sprintf(
            'INSERT INTO `stacks_%s` (`beginning_call_number`,'.
            ' `ending_call_number`, `range_number`, `std_beg`, `std_end`)'.
            ' VALUES("*", "*", "0", "%s", "%s")',
            mysql_real_escape_string($location_id),
            mysql_real_escape_string(standardize('0')),
            mysql_real_escape_string(standardize('Z', true))
        )) or die('Invalid query: ' . mysql_error());
    } elseif ($total_ranges > 1) {
        die('
            Error encountered: You shouldn\'t arrive here. Total ranges should
            not be more than one if you want to assign a location to this map.
        ');
    }
} elseif (isset($_POST['stackNo'])) {
    $set_mode = 'range';
    $stackNo = $_POST['stackNo'];

    // A map must be selected as current to assign icons to it.
    $sql3 = mysql_query('SELECT location_id FROM current');

    $location_id = '';

    if ($row3 = mysql_fetch_array($sql3)) {
        $location_id = $row3['location_id'];
    }
} else {
    die('Error: Parameter incorrect.');
}

$result = mysql_query(sprintf(
    'SELECT mapid, location FROM maps WHERE location_id = %s',
    mysql_real_escape_string($location_id)
));

$row = mysql_fetch_array($result);
$mapid = $row['mapid'];
$location = $row['location'];

$mapfile = '';

$sql = mysql_query(sprintf(
    'SELECT filename FROM mapimgs WHERE mapid = %s',
    mysql_real_escape_string($mapid)
));

if ($row = mysql_fetch_array($sql)) {
    $mapfile = $row['filename'];
}

$currenticon = 1;
$iconfile = '';

$sql2 = mysql_query(sprintf(
    'SELECT filename FROM iconassign, iconimgs WHERE iconid = icoid'.
    ' AND mapid = %s',
    mysql_real_escape_string($mapid)
));

if ($row2 = mysql_fetch_array($sql2)) {
    $iconfile = $row2['filename'];
}

// Fill the stack table with the x and y coordinates of the user's click
$result = mysql_query(sprintf(
    'UPDATE stacks_%s SET x_coord = %s, y_coord = %s WHERE range_number = %s',
    mysql_real_escape_string($location_id),
    mysql_real_escape_string($xCoord),
    mysql_real_escape_string($yCoord),
    mysql_real_escape_string($stackNo)
)) or die('Invalid query: ' . mysql_error());

// Query the database to ensure the data was added correctly
$xycoords = mysql_query(sprintf(
    'SELECT x_coord, y_coord FROM stacks_%s WHERE range_number = "%s"',
    mysql_real_escape_string($location_id),
    mysql_real_escape_string($stackNo)
));

while ($row = mysql_fetch_array($xycoords)) {
    $img_x_coord = $row['x_coord'];
    $img_y_coord = $row['y_coord'];
}

// Use the center of a 20px square image.
$img_y_coord -= 10;
$img_x_coord -= 10;

// Display feedback to the user including the map with a marker where
// he/she clicked

echo <<<END
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Stack Map</title>
<style media="screen">
END;

print '.noprint{position:absolute;top:';
print htmlspecialchars($img_y_coord) . 'px;left:';
print htmlspecialchars($img_x_coord) . 'px}';

echo <<<END
.noscreen{display:none;}
</style>
</head>

<body>
END;

if ($set_mode == 'location') {
    echo '<strong>Entry successfully added for location ';
    echo htmlspecialchars($location) . '!</strong>';
} elseif ($set_mode == 'range') {
    echo '<strong>Entry successfully added for range ';
    echo htmlspecialchars($stackNo) . '!</strong>';
}

echo <<<END
<br />
<a href="javascript:window.close()"">Close Window</a>
<br /><br />
<div style="position:absolute;">
END;

echo '<img src="../maps/' . htmlspecialchars($mapfile);
echo '" alt="Library stack map" />';

// Star placement for the computer screen
echo '<img class="noprint" src="../icons/' . htmlspecialchars($iconfile);
echo '" alt="Map marker" />';

echo <<<END
</div>
</body>
</html>
END;
