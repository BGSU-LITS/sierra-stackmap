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
echo '<div style="float: left; width: 25%">';
echo '<img src="arrowleft.gif">';
echo '</div>';

// Print out the beginning/ending ranges that were searched for
echo '<div style="float:left;width:50%;font-size:225%;text-align:left">';
echo '<strong>RANGE #' . htmlspecialchars($range_beg);
echo ' -- RANGE #' . htmlspecialchars($range_end);
echo '</strong><br><br>';
echo '</div>';

// Place right arrow
echo '<div style="float: left; width: 25%">';
echo '<img src="arrowright.gif">';
echo '</div>';

sqlConnect();

$query_results = get_chart($range_beg, $range_end);

print_table($query_results);

// Gets the stack chart between the beginning and end call numbers
function get_chart($range_beg, $range_end)
{
    $sql = mysql_query('SELECT location_id FROM current');

    $current_location = '';

    if ($row = mysql_fetch_array($sql)) {
        $current_location = $row['location_id'];
    }

    $sql = mysql_query(sprintf(
        'SELECT * FROM stacks_%s' .
        ' WHERE range_number >= "%s" AND range_number <= "%s"' .
        ' ORDER BY range_number',
        mysql_real_escape_string($current_location),
        mysql_real_escape_string($range_beg),
        mysql_real_escape_string($range_end)
    ));

    return $sql;
}

// Given the partial stack chart that it is passed, this function prints out a
// table for display on the outside of the library stacks.
function print_table($query_results)
{
    echo <<<END
<div style="font-family: verdana,helvetica,arial,sans-serif">
<table border="1" style="float:left;width:70%;font-size:70%;margin:0 auto">
END;

    // For each stack record found...
    while ($row = mysql_fetch_array($query_results)) {
        $beg_callno = $row['beginning_call_number'];
        $end_callno = $row['ending_call_number'];
        $range_no = $row['range_number'];

        echo '<tr>';
        echo '<td><strong>' . htmlspecialchars($range_no) . '</strong></td>';
        echo '<td>' . htmlspecialchars($beg_callno) . '</td>';
        echo '<td>' . htmlspecialchars($end_callno) . '</td>';
        echo '</tr>';
    }

    echo <<<END
</table>
</div>
END;
}
