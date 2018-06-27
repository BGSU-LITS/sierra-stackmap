<?php
/*  icons.php

    This file has functions included for the purposes of the index.php Stack
    Map Admin control panel. icons.php has a number of functions called by
    index.php's main menu that relate to printing and managing the icons that
    are available to use as markers on a map.
*/

// Creates the table of icons and displays them for viewing
// and deletion purposes
function iconList()
{
    $connect = sqlConnect();

    // Find all icons
    $result = mysqli_query($connect, 'select * from iconimgs')
    or die('Invalid query: ' . mysqli_error($connect));

    // Begin table
    $dynamicText = '
        <table cellpadding="0" cellspacing="0" id="stackList">
        <tr>
        <th>Icon name</th>
        <th>File name</th>
        <th>File size</th>
        <th>View</th>
        <th colspan="2">&nbsp;</th>
        </tr>
    ';

    // $number keeps track whether a record is odd or even --
    // used for shading purposes on the table
    $number = 1;

    // For each icon that has been uploaded...
    while ($d = mysqli_fetch_array($result)) {
        // Set icon's name, filename, and file size
        $name = $d['name'];
        $filename = $d['filename'];
        $icoid = $d['icoid'];

        $size = filesize('../icons/' . $filename) / 1000;

        $color = 'fffaef';

        if ($number % 2 == 1) {
            $color = 'ffffff';
        }

        // Print one row
        $dynamicText .= '
            <tr style="background:#' . htmlspecialchars($color) . '">
            <td>' . htmlspecialchars($name) . '</td>
            <td>' . htmlspecialchars($filename) . '</td>
            <td>' . htmlspecialchars($size) . ' K</td>
            <td><img src="../icons/' . htmlspecialchars($filename) . '"></td>
            <td><a href="index.php?section=icons&amp;mode=delete&amp;i=' .
                    htmlspecialchars($icoid) . '">
                <img border="0" src="img/delete.png"></a></td>
            </tr>
        ';

        $number++;
    }

    $dynamicText .= '</table>';

    return $dynamicText;
}

// Creates a form for uploading a new icon file
function uploadIcon()
{
    $dynamic = '
        <form enctype="multipart/form-data" method="post"
            action="index.php?section=icons&amp;mode=processupload">
        <fieldset><legend>Upload icon</legend>
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
        <label for="upload">File:&nbsp;</label>
        <input type="file" size="25" name="uploadedfile" id="upload"><br>
        <label for="iconname">Icon name:&nbsp;</label>
        <input type="text" size="25" name="iconname" id="iconname"><br>
        <input type="submit" value="Upload file">
        </fieldset>
        </form>
    ';

    return $dynamic;
}

// Prompts user yes/no regarding an icon deletion
function deleteIcon()
{
    $index = '';

    if (isset($_GET['i'])) {
        $index = $_GET['i'];
    }

    $dynamic = '
        <form action="index.php?section=icons&amp;mode=processdelete&amp;i=' .
            htmlspecialchars($index) . '" method="post">
        <fieldset><legend>Confirm delete</legend>
        Are you sure you want to delete this icon?&nbsp;<br>
        <input type="submit" name="submit" value="Yes">
        <input type="submit" name="submit" value="No">
        </fieldset>
        </form>
    ';

    return $dynamic;
}

// Prints out a list of all maps with a select (dropdown) box corresponding to
// each. Each box has all the icons and a "select" button to associate a new
// icon with one of the maps.
function assignIcon()
{
    $connect = sqlConnect();

    $result = mysqli_query($connect, 'select * from iconimgs')
    or die('Invalid query: ' . mysqli_error($connect));

    $result2 = mysqli_query($connect, 'select * from mapimgs')
    or die('Invalid query: ' . mysqli_error($connect));

    $dynamicText = '';

    // For each map that exists...
    while ($c = mysqli_fetch_array($result2)) {
        // Find that map's currently assigned icon
        $result3 = mysqli_query($connect, sprintf(
            'select iconid from iconassign where mapid = %s',
            mysqli_real_escape_string($connect, $c['mapid'])
        )) or die('Invalid query: ' . mysqli_error($connect));

        $e = mysqli_fetch_array($result3);

        // ... print the map's name and a dropdown box.
        $dynamicText .= '
            <form action="index.php" method="get">
            <label for="' .
                htmlspecialchars($c['name']) . '">' .
                htmlspecialchars($c['name']) . '</label><br>
            <select name="icons" id="' .
                htmlspecialchars($c['name']) . '">
        ';

        // For each icon that exists... add a dropdown box item.
        while ($d = mysqli_fetch_array($result)) {
            $name = $d['name'];

            // Does this icon match the currently assigned one?
            $selected = '';

            if ($d['icoid'] == $e['iconid']) {
                $selected = ' selected';
            }

            $dynamicText .= '
                <option value="' .
                    htmlspecialchars($name) . '"' .
                    htmlspecialchars($selected) . '>' .
                    htmlspecialchars($name) . '</option>
            ';
        }

        $dynamicText .= '
            </select>
            <input type="submit" value="Assign">
            <input type="hidden" name="section" value="icons">
            <input type="hidden" name="mode" value="processassign">
            <input type="hidden" name="map" id="hidden" value="' .
                htmlspecialchars($c['name']) . '"><br><br>
            </form>
        ';

        mysqli_data_seek($result, 0);
    }

    return $dynamicText;
}

// Update the database with a new icon-to-map assignment
// and give the user feedback.
function processAssign()
{
    $connect = sqlConnect();

    $map = '';

    if (isset($_GET['map'])) {
        $map = $_GET['map'];
    }

    $sql = mysqli_query($connect, sprintf(
        'SELECT mapid FROM mapimgs WHERE name = "%s"',
        mysqli_real_escape_string($connect, $map)
    ));

    $mapid = '';

    if ($row = mysqli_fetch_array($sql)) {
        $mapid = $row['mapid'];
    }

    $icons = '';

    if (isset($_GET['icons'])) {
        $icons = $_GET['icons'];
    }

    $sql = mysqli_query($connect, sprintf(
        'SELECT icoid FROM iconimgs WHERE name = "%s"',
        mysqli_real_escape_string($connect, $icons)
    ));

    $iconid = '';

    if ($row = mysqli_fetch_array($sql)) {
        $iconid = $row['icoid'];
    }

    mysqli_query($connect, sprintf(
        'UPDATE `iconassign` SET `iconid` = "%s" WHERE `mapid` = "%s" LIMIT 1',
        mysqli_real_escape_string($connect, $iconid),
        mysqli_real_escape_string($connect, $mapid)
    )) or die('Invalid query: ' . mysqli_error($connect));

    $dynamic = '
        <p>' . htmlspecialchars($icons) . ' has been successfully assigned
            to ' . htmlspecialchars($map) . '.</p>
    ';

    return $dynamic;
}
