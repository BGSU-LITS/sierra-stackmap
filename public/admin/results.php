<?php
/*  results.php
    Last update: 5/23/07
    Last modified by: Woon (woonkhang@gmail.com)
    Changes: Change sql statement so that it now prints the stacks ordered by range number
    
    results.php is called by the form in printstacks.php.  It prints out a chart of call numbers
    and their corresponding stack sections.
*/


/*  Utility file containing the sqlConnect function and login information for database connections. */

include("../connect.php");

$range_beg = $_POST['callbeg'];
$range_end = $_POST['callend'];

/* Place left arrow */

echo "<div style=\"float: left; width: 25%\">\n";
echo "<img src=\"arrowleft.gif\">\n";
echo "</div>\n";

/* Pring out the beginning/ending ranges that were searched for */

echo "<div style=\"float: left; width: 50%; font-size: 225%; text-align: left\">\n";
echo "<b>RANGE #". $range_beg . " -- RANGE #" . $range_end . "</b><br /><br />";
echo "</div>\n";

/* Place right arrow */

echo "<div style=\"float: left; width: 25%\">\n";
echo "<img src=\"arrowright.gif\">\n";
echo "</div>\n";


/*Add a spacer below header
echo "<div style=\"float: right; width: 15%\">\n";
//echo "blablabla\n";
//echo "&nbsp\n";
echo "</div>\n";*/


sqlConnect();

$query_results = get_chart($range_beg, $range_end);

print_table($query_results);




/* Gets the stack chart between the beginning and end call numbers */

function get_chart($range_beg, $range_end){
$sql = mysql_query("SELECT location_id FROM current");
if($row = mysql_fetch_array($sql))
  $current_location = $row['location_id'];
else
  $current_location ="";

$sql = mysql_query("SELECT * FROM stacks_$current_location WHERE
range_number >= \"$range_beg\" AND range_number <= \"$range_end\"
ORDER BY range_number");
return $sql;  
}

/*  Given the partial stack chart that it is passed, this function prints out a table
    for display on the outside of the library stacks. */

function print_table($query_results){

$record_number = 0;
//$half = mysql_num_rows($query_results) / 2;

echo "<div style=\"font-family: verdana,helvetica,arial,sans-serif;\">\n";
echo "<table border=\"1\" style=\"float: left; width: 70%; font-size: 70%; margin-left: auto;
    margin-right: auto\">\n";
//echo "<tr><td><b>Beginning Call #</b></td> <td><b>Ending Call #</b></td> <td><b>Range #</b></td> </tr>\n";

while($row = mysql_fetch_array($query_results))
      {
      //if($record_number == $half){
        //echo "</table>\n";
        //echo "<table border=\"1\" style=\"float: left; width: 45%; margin-left: 5%; font-size: 70%\">\n";
        //echo "<tr><td><b>Beginning Call #</b></td> <td><b>Ending Call #</b></td> <td><b>Range #</b></td> </tr>\n";
        //} end if regarding $half
      
      $beg_callno = $row['beginning_call_number'];
      $end_callno = $row['ending_call_number'];
      $range_no = $row['range_number'];
      
      echo "<tr>\n";  
      echo "<td><b>".$range_no."</b></td><td>".$beg_callno."</td><td>".$end_callno."</td>";
      echo "</tr>\n";
      $record_number++;
      }

echo "</table>\n";
echo "</div>\n";
}

/*//add right spacer
echo "<div style=\"float: left; width: 15%\">\n";
//echo "albalbalb\n";
//echo "&nbsp\n";
echo "</div>\n";*/

?>
