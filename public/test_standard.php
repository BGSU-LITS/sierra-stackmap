<?php
include 'standardize.php';
include 'connect.php';

?>

<form action="test_standard.php">
<input type="text" name="call" value="<?php echo (isset($_GET['call'])) ? $_GET['call'] : ''; ?>" />
<p>Range option:
<?php
if (isset($_GET['call_option']) && $_GET['call_option'] == 'end')
{
echo '<input type="radio" name="call_option" id="begin" value="begin" />
<label for="begin">Begin</label>
<input type="radio" name="call_option" id="end" value="end" checked="checked" />
<label for="end">End</label>';
}
else
{
echo '<input type="radio" name="call_option" id="begin" value="begin" checked="checked" />
<label for="begin">Begin</label>
<input type="radio" name="call_option" id="end" value="end" />
<label for="end">End</label>';
}

?>
</p>
<input type="submit" />
</form>

<?php
if (isset($_GET['call']))
{
	$call = $_GET['call'];
	echo "The original call no is: " . $call . "<br />";

    // DEBUG
    echo "After process: " . process($call) . "<br />";
    
    echo "Total parts: " . count_parts($call) . "<br />";
    
    $stan = ($_GET['call_option'] == 'begin') ? standardize($call) : standardize($call, true);
	
	echo "After standardized: " . $stan . "<br>";

	sqlConnect();
	$sql = mysql_query("SELECT * FROM `stacks_1` WHERE 
	std_beg <= \"$stan\" AND std_end >= \"$stan\"");

	while ($row = mysql_fetch_array($sql))
	{
		echo "Found in stack: " . $row['range_number'] . '<br>';
	}
}

/*$boolean = ("1A0000001.000000000.000000000.000000000" <= $stan) && ($stan <= "1N0006888.000000000.CZZZZZZZZ.ZZZZZZZZZ");
echo $boolean;

*/

/*
sqlConnect();

$sql = "select * from stacks_1";
$result1 = mysql_query($sql);

while ($d = mysql_fetch_array($result1))
{

$query = "UPDATE `stacks_1` SET `std_beg` = '".standardize($d['beginning_call_number'])."', `std_end` = '".standardize($d['ending_call_number'], true)."' WHERE `range_number` =".$d['range_number']." LIMIT 1";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());


}
*/
?>