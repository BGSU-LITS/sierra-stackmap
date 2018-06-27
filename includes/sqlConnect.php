<?php
// Utility function to facilitate database connection
function sqlConnect()
{
    static $link = false;

    if (!$link) {
        require __DIR__ .  '/../config/db.php';

        $link = mysqli_connect($db_host, $db_user, $db_password, $db_name);

        if (!$link) {
            die("Couldn't connect to DB: " . mysqli_connect_error());
        }
    }

    return $link;
}
