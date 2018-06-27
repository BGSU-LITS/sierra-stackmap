<?php
/*  stacksearch.php

    The purpose of this utility is to print a map for the user detailing where
    he/she can find a library resource with a call number matching the number
    from the user's search query.

    stacksearch.php is called by a form in the main index.html (name subject
    to change) search page. The data from the form is sent via GET to this PHP
    page and the table 'stacks' (in database rangeguide) is searched for the
    appropriate call number range. The stack area for that range is determined
    and a marker is placed on the stack map in the appropriate location.
*/

// Utility file containing the sqlConnect function and login information for
// database connections.
include('../includes/sqlConnect.php');

// Contains the algorithm for conversion of the user-specified call number to a
// number more suitable for an alphabetical database range search.
include('../includes/standardize.php');

$callnum = '';

if (!empty($_GET['callnumber'])) {
    // Update call number formatting (see above)
    $callnum = standardize($_GET['callnumber']);
}

// Connect to the 'rangeguide' database
$connect = sqlConnect();

// Came from a page that will require a selection
$location_id = '';

if (array_key_exists('location_id', $_GET)) {
    $location_id = $_GET['location_id'];
} else {
    $sql = mysqli_query($connect, 'SELECT location_id FROM current');

    if ($row = mysqli_fetch_array($sql)) {
        $location_id = $row['location_id'];
    }
}

$result = mysqli_query($connect, sprintf(
    'SELECT mapid FROM maps WHERE location_id = %s',
    mysqli_real_escape_string($connect, $location_id)
));

$mapid = mysqli_fetch_array($result)[0];

// The search takes advantage of the alphabetical nature of call numbers to
// find the appropriate number within the range
$sql = mysqli_query($connect, sprintf(
    'SELECT * FROM `%s` WHERE std_beg <= "%s" AND std_end >= "%s"',
    mysqli_real_escape_string($connect, 'stacks_' . $location_id),
    mysqli_real_escape_string($connect, $callnum),
    mysqli_real_escape_string($connect, $callnum)
));

// This second query is necessary now that the user can assign icons
// dynamically through the admin interface. It finds out which icon is assigned
// to the current map and gets the file name. Notice that mapid is set to the
// constant 1 presently -- that number can eventually be changed to a variable
// equal to the map id of whatever the "current map" is.
$sql3 = mysqli_query($connect, sprintf(
    'SELECT filename FROM mapimgs WHERE mapid = %s',
    mysqli_real_escape_string($connect, $mapid)
));

$mapfile = '';

if ($row3 = mysqli_fetch_array($sql3)) {
    $mapfile = $row3['filename'];
}

$sql2 = mysqli_query($connect, sprintf(
    'SELECT filename FROM iconassign, iconimgs WHERE iconid = icoid' .
    ' AND mapid = %s',
    mysqli_real_escape_string($connect, $mapid)
));

$iconfile = '';

if ($row2 = mysqli_fetch_array($sql2)) {
    $iconfile = $row2['filename'];
}

$sql4 = mysqli_query($connect, sprintf(
    'SELECT location FROM maps WHERE location_id = %s',
    mysqli_real_escape_string($connect, $location_id)
));

$location = '';

if ($row4 = mysqli_fetch_array($sql4)) {
    $location = $row4['location'];
}

// Iterate through the resultset and find the range number. Display it to the
// user. Although this works by looping through the array, it should _never_
// return more than one result.
while ($row = mysqli_fetch_array($sql)) {
    // This functionality may eventually be removed -- for now it's there to
    // reassure the user that the correct number was queried.
    echo '
        <br>&nbsp;&nbsp;Your selected call number ' .
        htmlspecialchars($_GET['callnumber']) . '
        was found in <strong>range ' .
        htmlspecialchars($row['range_number']) . '</strong> of ' .
        htmlspecialchars($location) . '.<br>
    ';

    $index = $row['range_number'];

    echo '
        &nbsp;&nbsp;Placing a marker
        <img src="./icons/' . htmlspecialchars($iconfile) . '" alt="Map marker"
            style="vertical-align:middle">
        for range ' . htmlspecialchars($index) . '...<br>
    ';
}

// Query the database again to find the x and y coordinates associated with
// the stack section that the previous search returned.
if (isset($index)) {
    // A valid stack number in range and the marker can be placed
    $xycoords = mysqli_query($connect, sprintf(
        'SELECT x_coord, y_coord FROM `%s` WHERE range_number = %s',
        mysqli_real_escape_string($connect, 'stacks_' . $location_id),
        mysqli_real_escape_string($connect, $index)
    ));

    while ($row = mysqli_fetch_array($xycoords)) {
        $img_x_coord = $row['x_coord'];
        $img_y_coord = $row['y_coord'];
    }

    // Use the center of a 20px square image.
    $img_y_coord -= 10;
    $img_x_coord -= 10;

    // Begin drawing map
    echo '
        <div style="position:absolute">
        <img src="./maps/' . htmlspecialchars($mapfile) . '"
            alt="Library stack map">
    ';

    // Star placement
    echo '
        <img style="position:absolute;width:20px;height:20px;top:' .
        htmlspecialchars($img_y_coord) . 'px;left:' .
        htmlspecialchars($img_x_coord) . '" src="./icons/' .
        htmlspecialchars($iconfile) . '" alt="Map marker">
        </div>
    ';
} else {
    // $index was never set, meaning the call number wasn't found in the db
    echo 'Invalid call number. Please try again...<br>';
}
?>
<script src="https://www.googletagmanager.com/gtag/js?id=UA-3319349-2"
    async></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'UA-3319349-2');
</script>
