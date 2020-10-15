<?php
include_once('./config.php');
include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');
$canConnectAndSelectDb = false;
$count = 30;
while (!$canConnectAndSelectDb && $count > 0)
{
    $connection = @mysqli_connect(
        DATABASE_HOST, DATABASE_USER, DATABASE_PASS
    );
    if ($connection)
    {
        $isDBSelected = @mysqli_select_db($connection, DATABASE_NAME);
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
