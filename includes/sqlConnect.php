<?php
/* Utility function to facilitate database connection */
function sqlConnect()
{
    require __DIR__ .  '/../config/db.php';

    $link = mysql_connect($db_host, $db_user, $db_password);

    if (!$link) {
        die("Couldn't connect to DB: " . mysql_error());
    }

    mysql_select_db($db_name) or die("Couldn't open DB: " .mysql_error());
}
