<?php
/*  stackchart.php
    Last update: 5/23/07
    Last modified by: Woon (woonkhang@gmail.com)

    Changes: change the sql query so that it prints the stack chart ORDERED by range_number
    
    stackchart.php prints a complete listing of all stacks and corresponding call numbers for
    the current map.  This utility is useful to librarians who may want to display a complete
    list of the stacks for reference on the outside of the stack shelves.  The format was modeled
    after the current BGSU stack displays.
*/

/*  Utility file containing the sqlConnect function and login information for database connections. */

include("../connect.php");

sqlConnect();

$query_results = get_chart();

print_date();

print_table($query_results);



/*  Grabs and returns a list of all stacks */


function get_chart(){
$sql = mysql_query("SELECT location_id FROM current");
if($row = mysql_fetch_array($sql))
  $current_location = $row['location_id'];
else
  $current_location ="";

$sql = mysql_query("SELECT * FROM stacks_$current_location ORDER BY range_number");
return $sql;    
}


/*  Add a timestamp so users know how recently the list was generated */

function print_date(){
echo "<div style=\"/*float: right;*/ font-size: 70%;\"> Updated: ".date("m/d/y")."</div><br />\n";
}



/* Prints and formats the list of stack ranges */

function print_table($query_results){


$record_number = 0;
$half = mysql_num_rows($query_results) / 2;

echo "<div style=\"font-family: verdana,helvetica,arial,sans-serif\">\n";
echo "<table border=\"1\" style=\"float: left; width: 45%; font-size: 70%\">\n";
echo "<tr><td><b>Beginning Call #</b></td> <td><b>Ending Call #</b></td> <td><b>Range #</b></td> </tr>\n";

/* For each stack record found... */

while($row = mysql_fetch_array($query_results))
      {
      if($record_number == $half){
        echo "</table>\n";
        echo "<table border=\"1\" style=\"float: left; width: 45%; margin-left: 5%; font-size: 70%\">\n";
        echo "<tr><td><b>Beginning Call #</b></td> <td><b>Ending Call #</b></td> <td><b>Range #</b></td> </tr>\n";
        }
      
      $beg_callno = $row['beginning_call_number'];
      $end_callno = $row['ending_call_number'];
      $range_no = $row['range_number'];
      
      echo "<tr>\n";  
      echo "<td>".$beg_callno."</td><td>".$end_callno."</td><td><b>".$range_no."</b></td>";
      echo "</tr>\n";
      $record_number++;
      }

echo "</table>\n";
echo "</div>\n";
}




?>
