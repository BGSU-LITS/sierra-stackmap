<?php

include("disambig.php");

print_first_half();

echo "<center>";
echo "Sorry, no map or text description was found for the location you selected.<br />";
$URL = $_SERVER['HTTP_REFERER'];
?>


<script type="text/javascript"> function closeWindow() { window.close(); } </script>
Click <a href='javascript:closeWindow()'>here</a> to close this window.
</center>

<?php

print_second_half();

?>
