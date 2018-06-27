<?php
/*  locations.php

    This file has functions included for the purposes of the index.php Stack
    Map Admin control panel. locations.php has a number of functions called by
    index.php's main menu that relate to printing and managing a library's
    various locations within (or even outside) the library.
*/


// Validates if the location is able to be map into 1 single location
// (it's true when there's no ranges assigned to that specific location)
function validateLocation($location_id)
{
    $connect = sqlConnect();

    // check to make sure no ranges exist in the location
    $result = mysqli_query($connect, sprintf(
        'SELECT count(*) as total_ranges from stacks_%s',
        mysqli_real_escape_string($connect, $location_id)
    ));

    $total_ranges = mysqli_fetch_array($result)[0];

    if ($total_ranges > 1) {
        // more than 1 range, don't mess with it
        return false;
    } elseif ($total_ranges == 1) {
        // if there's 1 range, make sure it's not assign to a specific
        // call number range
        $result = mysqli_query($connect, sprintf(
            'SELECT beginning_call_number, ending_call_number from stacks_%s',
            mysqli_real_escape_string($connect, $location_id)
        ));

        $row = mysqli_fetch_array($result);

        if ($row['beginning_call_number'] != '*'
         || $row['ending_call_number'] != '*') {
            return false;
        }
    }

    // Returns true, it's either 0 range or 1 range with all
    // call numbers range assign to one single location
    return true;
}

// Creates the table of locations and displays them for viewing and
// edit/deletion purposes
function printLocations()
{
    $connect = sqlConnect();

    // Needed to check whether a map has been assigned to a particular location
    $result = mysqli_query($connect, 'select * from maps ORDER BY location')
    or die('Invalid query: ' . mysqli_error($connect));

    $index = 0;

    // Begin the table
    $dynamicText = '
        <table cellpadding="0" cellspacing="0" id="stackList">
        <tr>
        <th>Name</th>
        <th>Link</th>
        <th>Map</th>
        <th colspan="3">&nbsp;</th>
        </tr>
    ';

    // For each location...
    while ($d = mysqli_fetch_array($result)) {
        $location_id = $d['location_id'];
        $name = $d['location'];
        $link = $d['text_link'];
        $is_map = $d['is_mapfile'];

        $color = 'fffaef';

        if ($index % 2 == 1) {
            $color = 'ffffff';
        }

        // If there's a map associated, find out which one
        if ($is_map) {
            $result3 = mysqli_query($connect, sprintf(
                'select mapid from maps where location = "%s"',
                mysqli_real_escape_string($connect, $name)
            )) or die('Invalid query: ' . mysqli_error($connect));

            $e = mysqli_fetch_array($result3);
            $mapid = $e['mapid'];

            $result2 = mysqli_query($connect, sprintf(
                'SELECT filename FROM mapimgs WHERE mapid = %s',
                mysqli_real_escape_string($connect, $mapid)
            )) or die('Invalid query: ' . mysqli_error($connect));

            $d = mysqli_fetch_array($result2);
            $is_map = $d['filename'];
        } else {
            $is_map = 'N/A';
        }

        $dynamicText .= '
            <tr style="background:#' . htmlspecialchars($color) . '">
            <td>' . htmlspecialchars($name) . '</td>
            <td><a href="' . htmlspecialchars($link) . '">' .
                htmlspecialchars($link) . '</a></td>
            <td>' . htmlspecialchars($is_map) . '</td>
        ';

        if ($is_map == 'N/A') {
            $dynamicText .= '<td>&nbsp;</td>';
        } elseif (validateLocation($location_id)) {
            $dynamicText .= '
                <td><a href="#" onclick="window.open(\'coor.php?location_id=' .
                        htmlspecialchars($location_id) . '\')">
                    <img border="0" src="img/mapit.gif" title="Map It!">
                    </a></td>
            ';
        } else {
            $dynamicText .= '
                <td><a href="#" onclick="window.open(\'coor.php?location_id=' .
                        htmlspecialchars($location_id) . '&amp;error=1\')">
                    <img border="0" src="img/mapitgrey.jpg" title="Map It!"
                        title="Some ranges are already assigned to this' .
                            ' location. Please delete all ranges first if' .
                            ' you want to assign this location to this map.' .
                            '">
                    </a></td>
            ';
        }

        $dynamicText .= '
            <td><a href="index.php?section=locations&amp;mode=edit&amp;i=' .
                    htmlspecialchars($location_id) . '">
                <img border="0" src="img/edit.png" alt="Edit"></a></td>
            <td><a href="index.php?section=locations&amp;mode=delete&amp;i=' .
                    htmlspecialchars($location_id) . '">
                <img border="0" src="img/delete.png" alt="Delete"></a></td>
            </tr>
        ';

        $index++;
    }

    $dynamicText .= '</table>';

    return $dynamicText;
}

// Prompts user yes/no regarding an location deletion
function deleteLocation()
{
    $index = '';

    if (isset($_GET['i'])) {
        $index = $_GET['i'];
    }

    $connect = sqlConnect();

    $result = mysqli_query($connect, sprintf(
        'SELECT location from maps where location_id = %s',
        mysqli_real_escape_string($connect, $index)
    )) or die('Invalid query: ' . mysqli_error($connect));

    $location = mysqli_fetch_array($result)[0];

    $dynamic = '
        <form action="index.php?section=locations&amp;mode=processdelete' .
            '&amp;i=' . htmlspecialchars($index) . '" method="post">
        <fieldset><legend>Confirm delete</legend>
        <strong>Warning:</strong>
        You\'ve selected to delete location: <strong>' .
        htmlspecialchars($location) . '</strong><br>
        <strong>ALL RANGES</strong> associated with this location will be
        <strong>REMOVED</strong> from the database!
        <br><br>
        Are you sure you want to proceed?&nbsp;<br>
        <input type="submit" name="submit" value="Yes">
        <input type="submit" name="submit" value="No">
        </fieldset>
        </form>
    ';

    return $dynamic;
}

// Creates a form for editing an already-existing location record
function editLocation()
{
    $connect = sqlConnect();

    $index = '';

    if (isset($_GET['i'])) {
        $index = $_GET['i'];
    }

    // Get the map and mapid based on the location name
    $result = mysqli_query($connect, sprintf(
        'select * from maps where location_id = %s',
        mysqli_real_escape_string($connect, $index)
    )) or die('Invalid query: ' . mysqli_error($connect));

    $d = mysqli_fetch_array($result);

    $result2 = mysqli_query($connect, 'select * from mapimgs')
    or die('Invalid query: ' . mysqli_error($connect));

    $result4 = mysqli_query($connect, sprintf(
        'select mapid from maps where location_id = %s',
        mysqli_real_escape_string($connect, $index)
    )) or die('Invalid query: ' . mysqli_error($connect));

    $f = mysqli_fetch_array($result4);
    $mapid = $f['mapid'];

    if (empty($mapid)) {
        $b = null;
    } else {
        $result3 = mysqli_query($connect, sprintf(
            'select name from mapimgs where mapid = %s',
            mysqli_real_escape_string($connect, $mapid)
        )) or die('Invalid query: ' . mysqli_error($connect));

        $b = mysqli_fetch_array($result3);
    }

    $location = $d['location'];
    $link = $d['text_link'];

    // Make a form and fill it with the pre-existing values for this record
    $dynamic = '
        <form action="index.php" method="get">
        <fieldset><legend>Details for location: ' .
            htmlspecialchars($location) . '</legend>
        <label for="location">Location Name:&nbsp;</label>
        <input type="text" size="25" name="location" id="location" value="' .
            htmlspecialchars($location) . '"><br>
        <label for="link">Link:&nbsp;</label>
        <input type="text" size="25" name="link" id="link" value="' .
            htmlspecialchars($link) . '"><br>
        <label for="maps">Map:&nbsp;</label>
        <select name="maps" id="maps">
        <option value="None">None</option>
    ';

    // Make the dropdown box with map names.  Show the currently selected map
    while ($c = mysqli_fetch_array($result2)) {
        $name = $c['name'];

        if (empty($b['name'])) {
            $b['name'] = 'None';
        }

        $selected = '';

        if ($name == $b['name']) {
            $selected = ' selected';
        }

        $dynamic .= '
            <option value="' .
                htmlspecialchars($name) . '"' .
                htmlspecialchars($selected) . '>' .
                htmlspecialchars($name) . '</option>
        ';
    }

    // Hidden variables that get sent to the next page
    $dynamic .= '
        </select>
        <input type="hidden" name="section" value="locations">
        <input type="hidden" name="mode" value="processedit">
        <input type="hidden" name="i" value="' .
            htmlspecialchars($index) . '">
        <input type="submit" value="Update">
        </fieldset></form>
    ';

    return $dynamic;
}

// Updates the database and provides feedback regarding the 'edit' that the
// user has submitted
function processEditLocation()
{
    $connect = sqlConnect();

    // This first segment checks to see if (and what) map has been assigned
    // to a location
    if (empty($_GET['maps']) || $_GET['maps'] == 'None') {
        $is_map = 0;
        $mapid = 'NULL';
    } else {
        $result = mysqli_query($connect, sprintf(
            'select mapid from mapimgs where name = "%s"',
            mysqli_real_escape_string($connect, $_GET['maps'])
        )) or die('Invalid query: ' . mysqli_error($connect));

        $d = mysqli_fetch_array($result);

        $is_map = 1;
        $mapid = $d['mapid'];
    }

    $location = '';

    if (isset($_GET['location'])) {
        $location = $_GET['location'];
    }

    $link = '';

    if (isset($_GET['link'])) {
        $link = $_GET['link'];
    }

    $i = '';

    if (isset($_GET['i'])) {
        $i = $_GET['i'];
    }

    // Update the database with the passed-in data
    $result = mysqli_query($connect, sprintf(
        'UPDATE `maps` SET `location` = "%s", `text_link` = "%s",' .
        ' `is_mapfile` = "%s", `mapid` = %s' .
        ' WHERE `location_id` = "%s" LIMIT 1',
        mysqli_real_escape_string($connect, $location),
        mysqli_real_escape_string($connect, $link),
        mysqli_real_escape_string($connect, $is_map),
        mysqli_real_escape_string($connect, $mapid),
        mysqli_real_escape_string($connect, $i)
    )) or die('Invalid query: ' . mysqli_error($connect));

    $dynamic =  '<p>Location has been successfully updated.</p>';

    return $dynamic;
}

// Form for creating a new location
function createLocation()
{
    $connect = sqlConnect();

    $result = mysqli_query($connect, 'select name from mapimgs')
    or die("Invalid query: " . mysqli_error($connect));

    $dynamic = '
        <form action="index.php" method="get">
        <fieldset><legend>Create New Location</legend>
        <label for="location">Location Name:&nbsp;</label>
        <input type="text" size="25" name="locname" id="location"><br>
        <label for="link">Link:&nbsp;</label>
        <input type="text" size="25" name="link" id="link"><br>
        <label for="maps">Map:&nbsp;</label>
        <select name="maps" id="maps">
        <option value="None">None</option>
    ';

    // Show a list of all the maps
    while ($c = mysqli_fetch_array($result)) {
        $name = $c['name'];

        $dynamic .= '
            <option value="' . htmlspecialchars($name) . '">' .
                htmlspecialchars($name) . '</option>
        ';
    }

    $dynamic .= '
        </select>
        <input type="hidden" name="section" value="locations">
        <input type="hidden" name="mode" value="processcreate"><br>
        <input type="submit" value="Add location">
        </fieldset></form>
    ';

    return $dynamic;
}

// Update the database by inserting a new location into it
function processCreateLocation()
{
    $connect = sqlConnect();

    // Determine whether (and what) map has been assigned to the new location
    if (empty($_GET['maps']) || $_GET['maps'] == 'None') {
        $is_map = 0;
        $mapid = 'NULL';
    } else {
        $result = mysqli_query($connect, sprintf(
            'select mapid from mapimgs where name = "%s"',
            mysqli_real_escape_string($connect, $_GET['maps'])
        )) or die('Invalid query: ' . mysqli_error($connect));

        $d = mysqli_fetch_array($result);

        $is_map = 1;
        $mapid = $d['mapid'];
    }

    $locname = '';

    if (isset($_GET['locname'])) {
        $locname = $_GET['locname'];
    }

    $link = '';

    if (isset($_GET['link'])) {
        $link = $_GET['link'];
    }

    // Update db
    $result = mysqli_query($connect, sprintf(
        'INSERT INTO `maps` (`location`, `text_link`, `is_mapfile`, `mapid`)' .
        ' VALUES ("%s", "%s", %s, %s)',
        mysqli_real_escape_string($connect, $locname),
        mysqli_real_escape_string($connect, $link),
        mysqli_real_escape_string($connect, $is_map),
        mysqli_real_escape_string($connect, $mapid)
    )) or die('Invalid query: ' . mysqli_error($connect));

    // create a new table `stacks_locid`
    $result = mysqli_query($connect, sprintf(
        'SELECT location_id from maps WHERE location = "%s"',
        mysqli_real_escape_string($connect, $locname)
    ));

    $loc_id = mysqli_fetch_array($result)[0];

    $result = mysqli_query($connect, sprintf(
        'CREATE TABLE `stacks_%s` (`beginning_call_number` varchar(50),' .
        ' `ending_call_number` varchar(50), `range_number` int(50),' .
        ' `std_beg` varchar(75), `std_end` varchar(75),' .
        ' `x_coord` smallint(4), `y_coord` smallint(4),' .
        ' PRIMARY KEY(`range_number`))',
        mysqli_real_escape_string($connect, $loc_id)
    )) or die('Invalid query: ' . mysqli_error($connect));

    $dynamic = '<p>Location has been successfully created.</p>';

    return $dynamic;
}
