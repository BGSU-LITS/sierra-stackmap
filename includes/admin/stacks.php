<?php
/*  stacks.php

    This file has functions included for the purposes of the index.php Stack
    Map Admin control panel. stacks.php has a number of functions called by
    index.php's main menu that relate to printing and managing a library's
    stack ranges as they pertain to a specific map.
*/

// Print out a list of all stack and call number ranges for the current map.
// Allows for editing and deletion of stack entries.
function stackList()
{
    $location_id = getCurrentLocationID();

    if ($location_id == '') {
        return errorNoMapSelected();
    }

    $connect = sqlConnect();

    $result = mysqli_query($connect, sprintf(
        'select * from stacks_%s ORDER BY range_number',
        mysqli_real_escape_string($connect, $location_id)
    )) or die('Invalid query: ' . mysqli_error($connect));

    // Begin a table to contain the stack records
    $dynamicText = '
        <table cellpadding="0" cellspacing="0" id="stackList">
        <tr>
        <th>Range Number</th>
        <th>Beginning Call No.</th>
        <th>End Call No.</th>
        <th colspan="3">&nbsp;</th>
        </tr>
    ';

    // For each range number found...
    $i = 0;

    while ($d = mysqli_fetch_array($result)) {
        $number = $d['range_number'];
        $begin = $d['beginning_call_number'];
        $end = $d['ending_call_number'];

        // Calculation to decide background color of each row
        $color = 'fff0cf';

        if ($i % 2 == 1) {
            $color = 'ffffff';
        }

        // not yet assign a map coordinate
        if ($d['x_coord'] == 0 && $d['y_coord'] == 0) {
            $color = 'd3dce3';
        }

        $dynamicText .= '
            <tr style="background:#' . htmlspecialchars($color) . '">
            <td>'. htmlspecialchars($number) . '</td>
            <td><a href="../stacksearch.php?callnumber='.
                    urlencode($begin === '*' ? 'A' : $begin) .
                    '&amp;location_id=' . $location_id . '">' .
                    htmlspecialchars($begin) . '</a></td>
            <td><a href="../stacksearch.php?callnumber='.
                    urlencode($end === '*' ? 'A' : $end) .
                    '&amp;location_id=' . $location_id . '">' .
                    htmlspecialchars($end) . '</a></td>
            <td><a onclick="window.open(\'coor.php?range=' .
                    htmlspecialchars($number) . '\')" href="#">
                <img border="0" src="img/mapit.gif" title="Map It!"></a></td>
            <td><a href="index.php?section=stacks&amp;mode=edit&amp;i=' .
                    htmlspecialchars($number) . '">
                <img border="0" src="img/edit.png" title="Edit"></a></td>
            <td><a href="index.php?section=stacks&amp;mode=delete&amp;i=' .
                    htmlspecialchars($number) . '">
                <img border="0" src="img/delete.png" title="Delete"></a></td>
            </tr>
        ';

        $i++;
    }

    $dynamicText .= '</table>';

    return $dynamicText;
}

// Displays a form for adding new stack ranges to the database.
// Specifically, this adds new ranges to the current map.
function addRange()
{
    $location_id = getCurrentLocationID();

    if ($location_id == '') {
        return errorNoMapSelected();
    }

    $dynamic = '
        <form action="index.php?section=stacks&amp;mode=processaddrange"
            method="get">
        <fieldset><legend>Add a range</legend>
        <label for="range">Range No.:&nbsp;</label>
        <input type="text" size="25" name="range" id="range" value=""><br>
        <label for="begin">Beginning Call No.:&nbsp;</label>
        <input type="text" size="25" name="begin" id="begin" value=""><br>
        <label for="end">Ending Call No.:&nbsp;</label>
        <input type="text" size="25" name="end" id="end" value=""><br>
        <input type="hidden" name="section" value="stacks">
        <input type="hidden" name="mode" value="processaddrange">
        <input type="submit" value="Add range">
        </fieldset>
        </form>
    ';

    return $dynamic;
}

// Adds the new range into the database and provides user feedback
function processAddRange()
{
    $range = '';

    if (isset($_GET['range'])) {
        $range = $_GET['range'];
    }

    $begin = '*';
    $begin_stan = '0';

    if (!empty($_GET['begin'])) {
        $begin = $begin_stan = $_GET['begin'];
    }

    $end = '*';
    $end_stan = 'Z';

    if (!empty($_GET['end'])) {
        $end = $end_stan = $_GET['end'];
    }

    $connect = sqlConnect();

    // Remember -- the range is added to the current map
    $location_id = getCurrentLocationID();

    // Note that the call numbers are run through standardize(), which is in
    // ../includes/standardize.php to put the call numbers into searchable
    // format. See that file for more details.
    $result = mysqli_query($connect, sprintf(
        'INSERT INTO `stacks_%s` (`beginning_call_number`,' .
        ' `ending_call_number`, `range_number`, `std_beg`, `std_end`)'.
        ' VALUES ("%s", "%s", "%s", "%s", "%s")',
        mysqli_real_escape_string($connect, $location_id),
        mysqli_real_escape_string($connect, $begin),
        mysqli_real_escape_string($connect, $end),
        mysqli_real_escape_string($connect, $range),
        mysqli_real_escape_string($connect, standardize($begin_stan)),
        mysqli_real_escape_string($connect, standardize($end_stan, true))
    )) or die('Invalid query: ' . mysqli_error($connect));

    $dynamic = '<p>Successfully added range ' . htmlspecialchars($range);
    $dynamic .= ' (' . htmlspecialchars($begin) . '-'. htmlspecialchars($end);
    $dynamic .= ').</p>';

    return $dynamic;
}

// After a user clicks the delete icon on a range, he/she is presented with
// this confirmation form
function deleteRange()
{
    $index = '';

    if (isset($_GET['i'])) {
        $index = $_GET['i'];
    }

    $dynamic = '
        <form action="index.php?section=stacks&amp;mode=processdeleterange"
            method="get">
        <fieldset><legend>Confirm delete</legend>
        Are you sure you want to delete range ' .
            htmlspecialchars($index) . '?&nbsp;<br>
        <input type="hidden" name="i" value="' .
            htmlspecialchars($index) . '">
        <input type="submit" name="submit" value="Yes">
        <input type="submit" name="submit" value="No">
        <input type="hidden" name="section" value="stacks">
        <input type="hidden" name="mode" value="processdeleterange">
        </fieldset>
        </form>
    ';

    return $dynamic;
}

// The delete is processed, and if successful, the database record is deleted
function processDeleteRange()
{
    if ((empty($_GET['i']) && $_GET['i'] !== '0')
     || empty($_GET['submit']) || $_GET['submit'] == 'No') {
        $dynamic = '<p>Delete canceled.</p>';
    } else {
        $range = $_GET['i'];

        $connect = sqlConnect();

        $location_id = getCurrentLocationID();

        $result = mysqli_query($connect, sprintf(
            'DELETE FROM `stacks_%s` WHERE `range_number` = %s LIMIT 1',
            mysqli_real_escape_string($connect, $location_id),
            mysqli_real_escape_string($connect, $range)
        )) or die('Invalid query: ' . mysqli_error($connect));

        $dynamic = '
            <p>Range ' . htmlspecialchars($range) . '
                has been successfully deleted.</p>
        ';
    }

    return $dynamic;
}

// A user can click the edit button from the 'view' screen to bring up this
// form. The form displays the current settings in the fields, which the user
// can then edit.
function editRange()
{
    $connect = sqlConnect();

    $index = '';

    if (isset($_GET['i'])) {
        $index = $_GET['i'];
    }

    $location_id = getCurrentLocationID();

    $result = mysqli_query($connect, sprintf(
        'select * from stacks_%s where range_number = %s',
        mysqli_real_escape_string($connect, $location_id),
        mysqli_real_escape_string($connect, $index)
    )) or die('Invalid query: ' . mysqli_error($connect));

    $d = mysqli_fetch_array($result);

    $begin = $d['beginning_call_number'];
    $end = $d['ending_call_number'];

    $dynamic = '
        <form action="index.php" method="get">
        <fieldset><legend>Details for Stack No. ' .
            htmlspecialchars($index) . '</legend>
        <label for="begin">Beginning Call No.:&nbsp;</label>
        <input type="text" size="25" name="begin" id="begin" value="'.
            htmlspecialchars($begin) . '"><br>
        <label for="end">Ending Call No.:&nbsp;</label>
        <input type="text" size="25" name="end" id="end" value="'.
            htmlspecialchars($end) . '"><br>
        <input type="hidden" name="section" value="stacks"><br>
        <input type="hidden" name="mode" value="processeditrange">
        <input type="hidden" name="i" value="'.
            htmlspecialchars($index) . '">
        <input type="submit" value="Update">
        </fieldset>
        </form>
    ';

    return $dynamic;
}

// Updates the database and provides feedback when the stack ranges have been
// modified
function processEditRange()
{
    $range = '';

    if (isset($_GET['i'])) {
        $range = $_GET['i'];
    }

    $begin = '*';
    $begin_stan = '0';

    if (!empty($_GET['begin'])) {
        $begin = $begin_stan = $_GET['begin'];
    }

    $end = '*';
    $end_stan = 'Z';

    if (!empty($_GET['end'])) {
        $end = $end_stan = $_GET['end'];
    }

    $connect = sqlConnect();

    $location_id = getCurrentLocationID();

    $result = mysqli_query($connect, sprintf(
        'UPDATE `stacks_%s` SET `beginning_call_number` = "%s",' .
        ' `ending_call_number` = "%s", `std_beg` = "%s", `std_end` = "%s"' .
        ' WHERE `range_number` = "%s" LIMIT 1',
        mysqli_real_escape_string($connect, $location_id),
        mysqli_real_escape_string($connect, $begin),
        mysqli_real_escape_string($connect, $end),
        mysqli_real_escape_string($connect, standardize($begin_stan)),
        mysqli_real_escape_string($connect, standardize($end_stan, true)),
        mysqli_real_escape_string($connect, $range)
    )) or die('Invalid query: ' . mysqli_error($connect));

    $dynamic = '
        <p>Range '. htmlspecialchars($range) . ' has been successfully
            updated.</p>
    ';

    return $dynamic;
}
