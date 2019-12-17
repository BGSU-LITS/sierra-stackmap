<?php
/*	Title: Library Data Parser (search.php)

    Purpose and Description: This page parses call number information out of
    the referring page, looks up the physical location of that resource in a
    database, then sends the user to a page with an appropriate map or link
    regarding how to locate the resource.

    Input: When the user clicks the "Map this" link, this php file takes all
    the text from the referring html file and uses it for input into call
    number and location arrays.

    Output: Essentially a link.  After the database is checked, this page
    simply redirects the user to a page at the determined link.

    Important Note:
    Depend on how the interface changes in the html search page, the code
    belows might need modifications.

    This search works by first parsing all the contents between
    <!-- field 1 --> and <!--end stackmap parse-->
*/

// Utility file containing the sqlConnect function and login information
// for database connections.
include('../includes/sqlConnect.php');


// Connect to the 'rangeguide' database.
$connect = sqlConnect();

// Declare arrays that will store the scraped data - by location, call number,
// and availability, respectively
$loc_arr = array();
$call_arr = array();

// This is for mapping of single entry only
$loc_arr[] = isset($_GET['loc_arr']) ? $_GET['loc_arr'] : "";
$call_arr[] = isset($_GET['call_arr']) ? $_GET['call_arr'] : "";

if (count($loc_arr) != count($call_arr)) {
    die('Error: Mismatch of number of locations and call numbers.');
}

// Connect to database and determine the link to which you need to redirect
$sql = mysqli_query($connect, sprintf(
    'SELECT text_link FROM maps WHERE location = "%s"',
    mysqli_real_escape_string($connect, urldecode($loc_arr[0]))
));

// link for no map
$link = "http://www.bgsu.edu/library/about/local-abbreviations.html";

while ($row = mysqli_fetch_array($sql)) {
    $link = $row['text_link'];
}

$sql2 = mysqli_query($connect, sprintf(
    'SELECT is_mapfile FROM maps WHERE location = "%s"',
    mysqli_real_escape_string($connect, urldecode($loc_arr[0]))
));

$ismap = 0;

while ($row = mysqli_fetch_array($sql2)) {
    $ismap = $row['is_mapfile'];
}

if ($ismap) {
    // Special exception because stacksearch.php is call number dependent
    $sql = mysqli_query($connect, sprintf(
        'SELECT location_id FROM maps WHERE location = "%s"',
        mysqli_real_escape_string($connect, urldecode($loc_arr[0]))
    ));

    $location_id = 0;

    if ($row = mysqli_fetch_array($sql)) {
        $location_id = $row['location_id'];
    }

    $link = 'stacksearch.php?callnumber=' .$call_arr[0];
    $link .= '&location_id=' . $location_id;
}

header('Location: ' . $link);
