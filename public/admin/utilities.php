<?php
/*  utilities.php
    Last update: 4/16/07
    
    This file has functions included for the purposes of the index.php Stack Map Admin control panel.
    utilities.php has a number of functions called by index.php's main menu that relate to specialized
    printing and testing of pre-entered stack ranges.
*/

/*  Redirects to a page that will print all stacks */

function printAllStacks(){
	$location_id = getCurrentLocationID();
	if ($location_id == "") {
		return errorNoMapSelected();
	}
header("Location: ./stackchart.php");

}

/*  Prompts the user with a form to print out a selected range of stack numbers */

function printPartialStacks(){
	$location_id = getCurrentLocationID();
	if ($location_id == "") {
		return errorNoMapSelected();
	}
$text = "<p>Use this form to print out only a range of stack call numbers.  You can print out
the chart that it generates to put on the end of the stacks. </p><br />

<p>Enter your desired <b>range number</b> of stacks below:</p> <br /> <br />

<form action=\"results.php\" method=\"post\">
From: 
<input type=\"text\" name=\"callbeg\">
 
To:  
<input type=\"text\" name=\"callend\">
<input type=\"submit\" />
</form>";

return $text;
}

/*  Creates a popup that contains stacksearch.php and places a marker on the call number
    that the user enters into the form */

function searchStacks(){
	$location_id = getCurrentLocationID();
	if ($location_id == "") {
		return errorNoMapSelected();
	}
$text ="
  <head>


    <SCRIPT TYPE=\"text/javascript\">
<!--
function popup()
{
window.open(\"\", \"_popup\", \"staus=yes, menubar=1, toolbar=no,location=0,width=850,height=900, resizable=yes\");
return true;
}
//-->
</SCRIPT>

  <title>BGSU Libraries Call Number Stack Search</title>
  </head>
  <body>

<!--<IMG SRC=\"button.php?s=36&text=PHP+is+Cool\">-->

<form action=\"../stacksearch.php\" method=\"GET\" target=\"_popup\" onsubmit=\"return popup()\">
<P>Enter call number:<P> <br />
<input type=\"text\" name=\"callnumber\" />
<input type=\"submit\" />
</form>
";
return $text;
}
?>
