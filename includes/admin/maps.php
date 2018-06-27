<?php
/*  maps.php

    This file has functions included for the purposes of the index.php Stack
    Map Admin control panel. maps.php has a number of functions called by
    index.php's main menu that relate to printing and managing a library's maps
    as they pertain to a specific location.
*/

// Create a table to list all the maps, their locations, and sizes.
// Alow for editing and deletion.
function mapList()
{
    $connect = sqlConnect();

    $query = 'select * from mapimgs';
    $result = mysqli_query($connect, $query)
    or die('Invalid query: ' . mysqli_error($connect));

    $dynamicText = '
        <table cellpadding="0" cellspacing="0" id="stackList">
        <tr>
        <th>Map name</th>
        <th>File name</th>
        <th>File size</th>
        <th>View file</th>
        <th colspan="2">&nbsp;</th>
        </tr>
    ';

    $number = 1;

    while ($d = mysqli_fetch_array($result)) {
        $name = $d['name'];
        $filename = $d['filename'];
        $mapid = $d['mapid'];

        // File size is calculated in kilobytes
        $size = filesize('../maps/' . $filename) / 1000;

        // This code is for the table -- it determines the color based on
        // whether it's an odd or even listing
        $color = 'fffaef';

        if ($number % 2 == 1) {
            $color = 'ffffff';
        }

        $dynamicText .= '
            <tr style="background:#' . htmlspecialchars($color) . '">
            <td>' . htmlspecialchars($name) . '</td>
            <td>' . htmlspecialchars($filename) . '</td>
            <td>' . htmlspecialchars($size) . ' K</td>
            <td><a href="../maps/' . htmlspecialchars($filename) . '">
                View</a></td>
            <td><a href="index.php?section=maps&amp;mode=delete&amp;i=' .
                htmlspecialchars($mapid) . '">
                <img border="0" src="delete.png"></a></td>
            </tr>
        ';

        $number++;
    }

    $dynamicText .= '</table>';

    return $dynamicText;
}

// Form for uploading a new map file
function uploadMap()
{
    $dynamic = '
        <form enctype="multipart/form-data" method="post"
            action="index.php?section=maps&amp;mode=processupload">
        <fieldset><legend>Upload map</legend>
        <input type="hidden" size="25" name="MAX_FILE_SIZE" value="1000000">
        <label for="upload">File:&nbsp;</label>
        <input type="file" size="25" name="uploadedfile" id="upload" value="">
        <br>
        <label for="mapname">Map name:&nbsp;</label>
        <input type="text" size="25" name="mapname" id="mapname" value="">
        <br>
        <input type="submit" value="Upload file">
        </fieldset>
        </form>
    ';

    return $dynamic;
}

// Confirms the user's delete selection
function deleteMap()
{
    $index = '';

    if (isset($_GET['i'])) {
        $index = $_GET['i'];
    }

    $dynamic = '
        <form action="index.php?section=maps&amp;mode=processdelete&amp;i=' .
            htmlspecialchars($index) . '" method="post">
        <fieldset><legend>Confirm delete</legend>
        Are you sure you want to delete this map?&nbsp;<br>
        <input type="submit" name="submit" value="Yes">
        <input type="submit" name="submit" value="No">
        </fieldset>
        </form>
    ';

    return $dynamic;
}

// Interface for selecting the "current map" that displays on every page under
// the menu. Creates a simple dropdown of available menus and shows the one
// that is currently selected as the default.
function selectLocation()
{
    $dynamic = '';

    $connect = sqlConnect();

    $query2 = 'select * from maps order by location';
    $result2 = mysqli_query($connect, $query2)
    or die('Invalid query: ' . mysqli_error($connect));

    $query3 = 'select location_id from current';
    $result3 = mysqli_query($connect, $query3)
    or die('Invalid query: ' . mysqli_error($connect));

    $e = mysqli_fetch_array($result3);

    $dynamic .= '
        <form id="currentMap" enctype="multipart/form-data"
            action="index.php" method="get">
        <select name="currentmap"
            onchange="document.getElementById(\'currentMap\').submit()">
        <option value="nomap">Please select a location...</option>
    ';

    while ($c = mysqli_fetch_array($result2)) {
        $location = $c['location'];
        $selected = '';

        if ($c['location_id'] == $e['location_id']) {
            $selected = ' selected';
        }

        $dynamic .= '
            <option' .
                htmlspecialchars($selected) . ' value="' .
                htmlspecialchars($location) . '">&nbsp;&nbsp;&nbsp;' .
                htmlspecialchars($location) . '</option>
        ';
    }

    $dynamic .= '
        </select>
        <input type="hidden" name="section" value="maps">
        <input type="hidden" name="mode" value="processcurrentselect">
        <input type="hidden" name="map" value="' .
            htmlspecialchars($c['name']) . '">
        </form>
        <br>
    ';

    return $dynamic;
}

// Once a new current map is selected the database needs to be updated.
// The user is given feedback about his/her selection too.
function processSelect()
{
    if (empty($_GET['currentmap']) || $_GET['currentmap'] == 'nomap') {
        return;
    }

    $connect = sqlConnect();

    $sql = mysqli_query($connect, sprintf(
        'SELECT location_id FROM maps WHERE location = "%s"',
        mysqli_real_escape_string($connect, $_GET['currentmap'])
    ));

    $location_id = '';

    if ($row = mysqli_fetch_array($sql)) {
        $location_id = $row['location_id'];
    }

    $sql = mysqli_query($connect, sprintf(
        'UPDATE `current` SET `location_id` = "%s"'.
        ' WHERE `location_id` is not null LIMIT 1',
        mysqli_real_escape_string($connect, $location_id)
    )) or die('Invalid query: ' . mysqli_error($connect));

    $dynamic = '
        <p>' . htmlspecialchars($_GET['currentmap']) . ' has been successfully
            set as the current location.</p>
    ';

    return $dynamic;
}
