<?php
/******************************************************
Title: Library Data Parser (search.php)

Last Modified: 6/11/07
by: Woon Tang (woonkhang@gmail.com)
Fixed problem with parsing an encrypted page (ie, when the user login to his/her library account).
Instead of getting the page content with php, a simple javascript get the page content and use POST submit method
to transfer the data to this php page.

Authors: 1.  Jared Contrascere
	-contact info-
		Email: nunzio@gmail.com
		AIM: Contrascere2005
		Mobile: (419)989-8967

	       2.  Dan Troha

Purpose and Description: This page parses call number information out of the
  referring page, looks up the physical location of that resource in a database,
  then sends the user to a page with an appropriate map or link regarding how
  to locate the resource.

Input:  When the user clicks the "Map this" link, this php file takes all the text
  from the referring html file and uses it for input into call number and location
  arrays.

Output: Essentially a link.  After the database is checked, this page simply
  redirects the user to a page at the determined link.

Important Note:
Depend on how the interface changes in the html search page, the code belows might need modifications.
This search works by first parsing all the contents between <!-- field 1 --> and <!--end stackmap parse-->

******************************************************/

/*  Utility file containing the sqlConnect function and login information for database connections. */

include('../includes/sqlConnect.php');


/*  Connect to the 'rangeguide' database. */

sqlConnect();

/*  Find the URL of the referring page for parsing */

//$URL = $_SERVER['HTTP_REFERER'];

/*  Connects back to the page specified in 'HTTP_REFERER' or $URL */

//$parsedPage = openURL($URL);

/* Get the whole page content from the html page  */
//$parsedPage = $_POST['parsed'];
//echo $parsedPage;

/*  Begins by scraping the entire recordset table out */

//$infoTable = strSelect($parsedPage, "<!-- field 1 -->", "<!--end stackmap parse-->");
//echo $infoTable . '--END>';die();
/*  Declare arrays that will store the scraped data - by location, call number, and availability, respectively */

$loc_arr = array();
$call_arr = array();

/* This is for mapping of multiple entries (since the mapit function is for each entry, this function is obsolete now */
//$loc_arr = isset($_GET['loc_arr']) ? explode(",", $_GET['loc_arr']) : "";
//$call_arr = isset($_GET['call_arr']) ? explode(",", $_GET['call_arr']) : "";

/* This is for mapping of single entry only */
$loc_arr[] = isset($_GET['loc_arr']) ? $_GET['loc_arr'] : "";
$call_arr[] = isset($_GET['call_arr']) ? $_GET['call_arr'] : "";

if (count($loc_arr) != count($call_arr))
{
	die("Error: Mismatch of number of locations and call numbers. Please notify Jerome Library ITS.");
}

//print_r($loc_arr);
//echo '<br>';
//print_r($call_arr);
/*  Begin filling arrays with each record */

// do
// {
  // /*  First, scrape the location name */

	// $location = strSelect($infoTable, "<a href=", "a");
	// $location = strSelect($location, ">", "<");
	// array_push($loc_arr, $location);

	// /* Scrape call number */

	// $call = strSelect($infoTable, "<!-- field C -->", "<!-- field % -->");
	// $call = strSelect($call, "<a href=", "a");
	// $call = strSelect($call, ">", "<");

	// /* Sometimes the call number doesn't have a link, in this case just grab the text. */

	// if($call == ""){
	    // $call = strSelect($infoTable, "<!-- field C -->", "<!-- field % -->");
	    // $call = strSelect($call, "<!-- field v -->", "<!-- field # -->");
    // }

	// array_push($call_arr, $call);

	// /* Scraping availability of resource */

	// $avail = strSelect($infoTable, "<!-- field % -->", "td");
	// $avail = strSelect($avail, " ", "<");
	// array_push($avail_arr, $avail);

	// echo "location = " . $location . "<br>";
	// echo "call = " . $call . "<br>";
	// echo "avail = " . $avail . "<br>";

	// $found = strpos($infoTable, "<!-- field 1 -->");  //should be zero (false) if no other entries exist.

	// if ($found)
		// $infoTable = strSelect($infoTable, "<!-- field 1 -->", "");	//trims the data table to the next entry

// } while ($found);	//will break when there are no more entries in the table


  /* Connect to database and determine the link to which you need to redirect */

  $sql = mysql_query("SELECT text_link FROM maps WHERE location = \"$loc_arr[0]\"");
    while($row = mysql_fetch_array($sql))
      {
      $link = $row['text_link'];
    }

  $sql2 = mysql_query("SELECT is_mapfile FROM maps WHERE location = \"$loc_arr[0]\"");
    while($row = mysql_fetch_array($sql2))
      {
      $ismap = $row['is_mapfile'];
    }

  if(!isset($ismap)){
  $ismap = 0;
  }

  if(!isset($link)){
  $link = "http://www.bgsu.edu/library/about/local-abbreviations.html";	// link for no map
  }

  if($ismap){  //Special exception because stacksearch.php is call number dependent


        $sql = mysql_query("SELECT location_id FROM maps WHERE
        location = \"$loc_arr[0]\"");

        if($row = mysql_fetch_array($sql))
          {
          $location_id = $row['location_id'];
        }
        else{
          $location_id = 0;
        }

        header("Location: stacksearch.php?callnumber=$call_arr[0]&location_id=$location_id");
      }
      else{
        header("Location: $link");
      }




/*  Connects back to the page specified in $URL and returns back its contents */

function openURL($URL)
{
	$handle = fopen($URL, "r") or die("Error opening URL.  Please notify Jerome Library ITS.");

	$contents = '';
	while (!feof($handle))
	{
  		$contents .= fread($handle, 8192);
	}
	fclose($handle);

	return $contents;
}


/*  Selects the data between $before and $after */

function strSelect($haystack, $before, $after)
{
	$begin = stristr($haystack, $before);
	$end = stripos($begin, $after) - strlen($before);
	$selected = substr($begin, strlen($before), $end);

//	echo "begin: " . $begin . "<br>";
//	echo "end: " . $end . "<br>";
//	echo "selected: " . $selected . "<br>";
//	die();

	return $selected;
}

?>
