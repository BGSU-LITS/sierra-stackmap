<?php
/*  results.php

    Called by the form in printstacks.php. It prints out a chart of call
    numbers and their corresponding stack sections.
*/

// Utility file containing the sqlConnect function and login information for
// database connections.

include('../../includes/sqlConnect.php');

$range_beg = '';
$range_end = '';

if (isset($_POST['callbeg'])) {
    $range_beg = $_POST['callbeg'];
}

if (isset($_POST['callend'])) {
    $range_end = $_POST['callend'];
}

// Place left arrow
echo '
    <div style="float: left; width: 25%">
    <img src="arrowleft.gif">
    </div>
';

// Print out the beginning/ending ranges that were searched for
echo '
    <div style="float:left;width:50%;font-size:225%;text-align:left">
    <strong>RANGE #' .
    htmlspecialchars($range_beg) . ' -- RANGE #' .
    htmlspecialchars($range_end) . '</strong><br><br>
    </div>
';

// Place right arrow
echo '
    <div style="float: left; width: 25%">
    <img src="arrowright.gif">
    </div>
';

$query_results = get_chart($range_beg, $range_end);

print_table($query_results);

// Gets the stack chart between the beginning and end call numbers
function get_chart($range_beg, $range_end)
{
    $connect = sqlConnect();

    $sql = mysqli_query($connect, 'SELECT location_id FROM current');

    $current_location = '';

    if ($row = mysqli_fetch_array($sql)) {
        $current_location = $row['location_id'];
    }

    $sql = mysqli_query($connect, sprintf(
        'SELECT * FROM stacks_%s' .
        ' WHERE range_number >= "%s" AND range_number <= "%s"' .
        ' ORDER BY range_number',
        mysqli_real_escape_string($connect, $current_location),
        mysqli_real_escape_string($connect, $range_beg),
        mysqli_real_escape_string($connect, $range_end)
    ));

    return $sql;
}

// Given the partial stack chart that it is passed, this function prints out a
// table for display on the outside of the library stacks.
function print_table($query_results)
{
    echo '
        <div style="font-family: verdana,helvetica,arial,sans-serif">
        <table style="float:left;width:70%;font-size:70%;margin:0 auto"
            border="1">
    ';

    // For each stack record found...
    while ($row = mysqli_fetch_array($query_results)) {
        $beg_callno = $row['beginning_call_number'];
        $end_callno = $row['ending_call_number'];
        $range_no = $row['range_number'];

        echo '
            <tr>
            <td><strong>' . htmlspecialchars($range_no) . '</strong></td>
            <td>' . htmlspecialchars($beg_callno) . '</td>
            <td>' . htmlspecialchars($end_callno) . '</td>
            </tr>
        ';
    }

    echo '
        </table>
        </div>
    ';
}
