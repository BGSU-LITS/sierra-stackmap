<?php
/*  stackchart.php

    Prints a complete listing of all stacks and corresponding call numbers for
    the current map. This utility is useful to librarians who may want to
    display a complete list of the stacks for reference on the outside of the
    stack shelves. The format was modeled after the current BGSU stack displays.
*/

// Utility file containing the sqlConnect function and login information for
// database connections.
include('../../includes/sqlConnect.php');

$query_results = get_chart();

print_date();

print_table($query_results);

// Grabs and returns a list of all stacks
function get_chart()
{
    $connect = sqlConnect();

    $sql = mysqli_query($connect, 'SELECT location_id FROM current');

    $current_location = '';

    if ($row = mysqli_fetch_array($sql)) {
        $current_location = $row['location_id'];
    }

    $sql = mysqli_query($connect, sprintf(
        'SELECT * FROM stacks_%s ORDER BY range_number',
        mysqli_real_escape_string($connect, $current_location)
    ));

    return $sql;
}

// Add a timestamp so users know how recently the list was generated
function print_date()
{
    echo '
        <div style="font-size: 70%;">Updated: ' .
        date('m/d/y') . '</div><br>
    ';
}

// Prints and formats the list of stack ranges
function print_table($query_results)
{
    $record_number = 0;
    $half = ceil(mysqli_num_rows($query_results) / 2);

    echo '
        <div style="font-family: verdana,helvetica,arial,sans-serif">
        <table border="1" style="float:left;width:45%;font-size:70%">
        <tr>
        <th align="left">Beginning Call #</th>
        <th align="left">Ending Call #</th>
        <th align="left">Range #</th>
        </tr>
    ';

    // For each stack record found...
    while ($row = mysqli_fetch_array($query_results)) {
        if ($record_number == $half) {
            echo '
                </table>
                <table border="1"
                    style="float:left;width:45%;margin-left:5%;font-size:70%">
                <tr>
                <th align="left">Beginning Call #</th>
                <th align="left">Ending Call #</th>
                <th align="left">Range #</th>
                </tr>
            ';
        }

        $beg_callno = $row['beginning_call_number'];
        $end_callno = $row['ending_call_number'];
        $range_no = $row['range_number'];

        echo '
            <tr>
            <td>' . htmlspecialchars($beg_callno) . '</td>
            <td>' . htmlspecialchars($end_callno) . '</td>
            <td><strong>' . htmlspecialchars($range_no) . '</strong></td>
            </tr>
        ';

        $record_number++;
    }

    echo '
        </table>
        </div>
    ';
}
