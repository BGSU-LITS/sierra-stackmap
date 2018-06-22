<?php
/*  utilities.php

    This file has functions included for the purposes of the index.php Stack Map Admin control panel.
    utilities.php has a number of functions called by index.php's main menu that relate to specialized
    printing and testing of pre-entered stack ranges.
*/

// Redirects to a page that will print all stacks
function printAllStacks()
{
    $location_id = getCurrentLocationID();

    if ($location_id == '') {
        return errorNoMapSelected();
    }

    header('Location: ./stackchart.php');
    exit;
}

// Prompts the user with a form to print out a selected range of stack numbers
function printPartialStacks()
{
    $location_id = getCurrentLocationID();

    if ($location_id == '') {
        return errorNoMapSelected();
    }

    $text = <<<END
<p>Use this form to print out only a range of stack call numbers. You can print
    out the chart that it generates to put on the end of the stacks.</p>
<p>Enter your desired <b>range number</b> of stacks below:</p>

<form action="results.php" method="post">
From:
<input type="text" name="callbeg">

To:
<input type="text" name="callend">
<input type="submit">
</form>
END;

    return $text;
}

// Creates a popup that contains stacksearch.php and places a marker on the
// call number that the user enters into the form
function searchStacks()
{
    $location_id = getCurrentLocationID();

    if ($location_id == '') {
        return errorNoMapSelected();
    }

    $text = <<<END
<head>
<script>
function popup()
{
    window.open(
        '',
        'mapit',
        'width=800,height=650,menubar=1,resizable=1,scrollbars=1'
    );

    return true;
}
</script>
<title>BGSU Libraries Call Number Stack Search</title>
</head>
<body>
<form action="../stacksearch.php" method="GET" target="mapit" onsubmit="return popup()">
<P>Enter call number:<br>
<input type="text" name="callnumber">
<input type="submit">
</form>
END;

    return $text;
}
