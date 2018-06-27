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

$connect = sqlConnect();

if (isset($_POST['location_id'])) {
    $set_mode = 'location';
    $location_id = $_POST['location_id'];

    // We use range number 0 as the placeholder to store the x-y position of
    // the location in this map
    $stackNo = 0;

    // check the total ranges (must be either one or zero)
    $result = mysqli_query($connect, sprintf(
        'SELECT count(*) as total_ranges from stacks_%s',
        mysqli_real_escape_string($connect, $location_id)
    ));

    $total_ranges = mysqli_fetch_array($result)[0];

    if ($total_ranges == 0) {
        // insert a new row in stacks_locid, so that later it can be updated
        // with the new map coordinate values.
        $result = mysqli_query($connect, sprintf(
            'INSERT INTO `stacks_%s` (`beginning_call_number`,'.
            ' `ending_call_number`, `range_number`, `std_beg`, `std_end`)'.
            ' VALUES("*", "*", "0", "%s", "%s")',
            mysqli_real_escape_string($connect, $location_id),
            mysqli_real_escape_string($connect, standardize('0')),
            mysqli_real_escape_string($connect, standardize('Z', true))
        )) or die('Invalid query: ' . mysqli_error($connect));
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
    $sql3 = mysqli_query($connect, 'SELECT location_id FROM current');

    $location_id = '';

    if ($row3 = mysqli_fetch_array($sql3)) {
        $location_id = $row3['location_id'];
    }
} else {
    die('Error: Parameter incorrect.');
}

$result = mysqli_query($connect, sprintf(
    'SELECT mapid, location FROM maps WHERE location_id = %s',
    mysqli_real_escape_string($connect, $location_id)
));

$row = mysqli_fetch_array($result);
$mapid = $row['mapid'];
$location = $row['location'];

$mapfile = '';

$sql = mysqli_query($connect, sprintf(
    'SELECT filename FROM mapimgs WHERE mapid = %s',
    mysqli_real_escape_string($connect, $mapid)
));

if ($row = mysqli_fetch_array($sql)) {
    $mapfile = $row['filename'];
}

$currenticon = 1;
$iconfile = '';

$sql2 = mysqli_query($connect, sprintf(
    'SELECT filename FROM iconassign, iconimgs WHERE iconid = icoid'.
    ' AND mapid = %s',
    mysqli_real_escape_string($connect, $mapid)
));

if ($row2 = mysqli_fetch_array($sql2)) {
    $iconfile = $row2['filename'];
}

// Fill the stack table with the x and y coordinates of the user's click
$result = mysqli_query($connect, sprintf(
    'UPDATE stacks_%s SET x_coord = %s, y_coord = %s WHERE range_number = %s',
    mysqli_real_escape_string($connect, $location_id),
    mysqli_real_escape_string($connect, $xCoord),
    mysqli_real_escape_string($connect, $yCoord),
    mysqli_real_escape_string($connect, $stackNo)
)) or die('Invalid query: ' . mysqli_error($connect));

// Query the database to ensure the data was added correctly
$xycoords = mysqli_query($connect, sprintf(
    'SELECT x_coord, y_coord FROM stacks_%s WHERE range_number = "%s"',
    mysqli_real_escape_string($connect, $location_id),
    mysqli_real_escape_string($connect, $stackNo)
));

while ($row = mysqli_fetch_array($xycoords)) {
    $img_x_coord = $row['x_coord'];
    $img_y_coord = $row['y_coord'];
}

// Use the center of a 20px square image.
$img_y_coord -= 10;
$img_x_coord -= 10;

// Display feedback to the user including the map with a marker where
// he/she clicked
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Stack Map</title>
<style media="screen">
<?php
echo '
    .noprint{position:absolute;top:' .
        htmlspecialchars($img_y_coord) . 'px;left:' .
        htmlspecialchars($img_x_coord) . 'px}
';
?>
.noscreen{display:none;}
</style>
</head>
<body>
<?php
if ($set_mode == 'location') {
    echo '
        <strong>Entry successfully added for location ' .
        htmlspecialchars($location) . '!</strong>
    ';
} elseif ($set_mode == 'range') {
    echo '
        <strong>Entry successfully added for range ' .
        htmlspecialchars($stackNo) . '!</strong>
    ';
}
?>
<br>
<a href="javascript:window.close()">Close Window</a>
<br><br>
<div style="position:absolute;">
<?php
echo '
    <img alt="Library stack map" src="../maps/' .
        htmlspecialchars($mapfile) . '">
';

// Star placement for the computer screen
echo '
    <img alt="Map marker" class="noprint"
        src="../icons/' . htmlspecialchars($iconfile) . '">
';
?>
</div>
</body>
</html>
