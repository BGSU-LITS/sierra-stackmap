<?php
/*  coor.php

    The purpose of this file is to display the current map and allow the user
    to assign coordinates to a stack/library map.
*/

include('../../includes/sqlConnect.php');

$connect = sqlConnect();

// Retrieve the file for the current map
$location_id = '';
$range = '';

if (isset($_GET['location_id'])) {
    // Set the location (ie, all ranges in the location) to a map
    $set_mode = 'location';
    $location_id = $_GET['location_id'];

    if (isset($_GET['error'])) {
        die('
            Some ranges are already assigned to this location.<br>
            Please delete all ranges first if you want to assign this location
            to this map.
        ');
    }
} elseif (isset($_GET['range'])) {
    // set the range location to a map
    $set_mode = 'range';
    $range = $_GET['range'];

    $sql = mysqli_query($connect, 'SELECT location_id FROM current');

    if ($row = mysqli_fetch_array($sql)) {
        $location_id = $row['location_id'];
    }
}

$result = mysqli_query($connect, sprintf(
    'SELECT mapid, location FROM maps WHERE location_id = %s',
    mysqli_real_escape_string($connect, $location_id)
)) or die('Invalid query: ' . mysqli_error($connect));

$row = mysqli_fetch_array($result);
$mapid = $row['mapid'];
$location = $row['location'];

$sql = mysqli_query($connect, sprintf(
    'SELECT filename FROM mapimgs WHERE mapid = %s',
    mysqli_real_escape_string($connect, $mapid)
));

$mapfile = '';

if ($row = mysqli_fetch_array($sql)) {
    $mapfile = $row['filename'];
}

if ($set_mode == "location") {
    $instructions = '
        <strong>You\'ve selected location: ' .
            htmlspecialchars($location) . '.</strong><br>
        <p style="color:red;font-weight:bold">
            Please click on the map location where you would like that
            location to be assigned to.</p>
    ';
} elseif ($set_mode == 'range') {
    $instructions = '
        <strong>You\'ve selected range no: ' .
            htmlspecialchars($range) . '.</strong><br>
        <p style="color:red;font-weight:bold">
            Please click on the map location where you would like that range
            number to be assigned to. For example, to assign range 1 to a
            section on the map, simply click in the middle of range 1 on the
             map below.</p>
    ';
}

// Print the coordinate assignment interface.
// Note that the form posts to x.php.
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Stack Map</title>
</head>

<body>
<?php echo $instructions; ?>
<form action="x.php" method="post">

<?php
// Print the dynamically retrieved map file
echo '
    <input type="image" value="Submit" alt="Submit" src="../maps/' .
        htmlspecialchars($mapfile) . '">
';

if ($set_mode == 'location') {
    echo '
        <input type="hidden" name="location_id" value="' .
            htmlspecialchars($location_id) . '">
    ';
} elseif ($set_mode == 'range') {
    echo '
        <input type="hidden" name="stackNo" value="'.
            htmlspecialchars($range) . '">
    ';
}
?>

</form>
</body>
</html>
