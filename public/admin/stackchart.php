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

sqlConnect();

$query_results = get_chart();

print_date();

print_table($query_results);

// Grabs and returns a list of all stacks
function get_chart()
{
    $sql = mysql_query('SELECT location_id FROM current');

    $current_location = '';

    if ($row = mysql_fetch_array($sql)) {
        $current_location = $row['location_id'];
    }

    $sql = mysql_query(sprintf(
        'SELECT * FROM stacks_%s ORDER BY range_number',
        mysql_real_escape_string($current_location)
    ));

    return $sql;
}

// Add a timestamp so users know how recently the list was generated
function print_date()
{
    echo '<div style="font-size: 70%;">Updated: ';
    echo date('m/d/y') . '</div><br>';
}

// Prints and formats the list of stack ranges
function print_table($query_results)
{
    $record_number = 0;
    $half = ceil(mysql_num_rows($query_results) / 2);

    echo <<<END
<div style="font-family: verdana,helvetica,arial,sans-serif">
<table border="1" style="float:left;width:45%;font-size:70%">
<tr>
<th align="left">Beginning Call #</th>
<th align="left">Ending Call #</th>
<th align="left">Range #</th>
</tr>
END;

    // For each stack record found...
    while ($row = mysql_fetch_array($query_results)) {
        if ($record_number == $half) {
            echo <<<END
</table>
<table border="1" style="float:left;width:45%;margin-left:5%;font-size:70%">
<tr>
<th align="left">Beginning Call #</th>
<th align="left">Ending Call #</th>
<th align="left">Range #</th>
</tr>

END;
        }

        $beg_callno = $row['beginning_call_number'];
        $end_callno = $row['ending_call_number'];
        $range_no = $row['range_number'];

        echo '<tr>';
        echo '<td>' . htmlspecialchars($beg_callno) . '</td>';
        echo '<td>' . htmlspecialchars($end_callno) . '</td>';
        echo '<td><strong>' . htmlspecialchars($range_no) . '</strong></td>';
        echo '</tr>';

        $record_number++;
    }

    echo <<<END
</table>
</div>
END;
}
