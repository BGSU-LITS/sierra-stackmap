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
<p>Enter your desired <strong>range number</strong> of stacks below:</p>

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
<p><label for="callnumber">Enter call number:</label>
<input type="text" id="callnumber" name="callnumber">
<input type="submit"></p>
</form>
END;

    return $text;
}

// Tests standardization of a call number.
function testStandard()
{
    $text = '
        <form action="index.php" method="GET">
        <input type="hidden" name="section" value="utilities">
        <input type="hidden" name="mode" value="teststandard">
        <p><label for="begin">Range option:</label>
        <label for="begin">
            <input type="radio" name="call_option" id="begin" value="begin"' .
                (isset($_GET['call_option']) && $_GET['call_option'] == 'end'
                    ? '>' : ' checked>') . ' Begin
        </label>
        <label for="end">
            <input type="radio" name="call_option" id="end" value="end"' .
                (isset($_GET['call_option']) && $_GET['call_option'] == 'end'
                    ? ' checked>' : '>') . ' End
        </label>
        </p>

        <p><label for="call">Enter call number:</label>
        <input type="text" id="call" name="call" value=' .
            htmlspecialchars(isset($_GET['call']) ? $_GET['call'] : '') . '>
        <input type="submit"></p>
        </form>
    ';

    if (isset($_GET['call'])) {
        $call = $_GET['call'];

        $text .= '<p>The original call no is: ';
        $text .= htmlspecialchars($call) . '<br>';

        // DEBUG
        $text .= 'After processed: ';
        $text .= htmlspecialchars(process($call)) . '<br>';

        $text .= 'Total parts: ';
        $text .= htmlspecialchars(count_parts($call)) . '<br>';

        $stan = standardize($call, $_GET['call_option'] == 'begin');

        $text .= 'After standardized: ';
        $text .= htmlspecialchars($stan) . '<br>';

        sqlConnect();

        $sql = mysql_query(sprintf(
            'SELECT * FROM `stacks_1`' .
            ' WHERE std_beg <= "%s" AND std_end >= "%s"',
            mysql_real_escape_string($stan),
            mysql_real_escape_string($stan)
        ));

        while ($row = mysql_fetch_array($sql)) {
            $text .= 'Found in stack: ';
            $text .= htmlspecialchars($row['range_number']) . '<br>';
        }
    }

    return $text;
}
