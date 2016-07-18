<?php
include_once('./config.php');
include_once('./constants.php');
include_once('./lib/DatabaseConnection.php');
$canConnectAndSelectDb = false;
$count = 30;
while (!$canConnectAndSelectDb && $count > 0)
{
    $connection = @mysql_connect(
        DATABASE_HOST, DATABASE_USER, DATABASE_PASS
    );
    if ($connection)
    {
        $isDBSelected = @mysql_select_db(DATABASE_NAME, $connection);
        if ($isDBSelected)
        {
            $canConnectAndSelectDb = true;
        }
    }
    sleep(1);
    --$count;
}
if ($canConnectAndSelectDb) {
    exit(0);
} else {
    echo "Timeout while waiting for the DB and database.\n";
    exit(1);
}
?>
