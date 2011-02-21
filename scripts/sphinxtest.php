<?php

/* Site for which to filter results. */
define('TEST_QUERY', 'java');

/* Site for which to filter results. */
define('TEST_SITE_ID', 201);

/* We want some error output. */
error_reporting(E_ERROR);

/* This is probably getting called from cron, so we have to figure out
 * where we are and where CATS is.
 */
$CATSHome = realpath(dirname(__FILE__) . '/../');
include(realpath($CATSHome . '/config.php'));

if (php_sapi_name() == 'cli')
{
    $stderr = STDERR;
    $stdout = STDOUT;
}
else
{
    $stderr = fopen('php://output', 'w');
    $stdout = fopen('php://output', 'w');
}

if (!defined('ENABLE_SPHINX'))
{
    fwrite($stderr, "Config Error: ENABLE_SPHINX is not defined.\n");
    exit(1);
}

if (!ENABLE_SPHINX)
{
    fwrite($stdout, "Sphinx is disabled in config.php.\n");
    exit(0);
}

$SphinxAPI = realpath($CATSHome . '/' . SPHINX_API);
if (!file_exists($SphinxAPI))
{
    fwrite($stderr, "Config Error: SPHINX_API could not be found.\n");
    exit(1);
}

include($SphinxAPI);

/* Sphinx API likes to throw PHP errors *AND* use it's own error
 * handling.
 */
assert_options(ASSERT_WARNING, 0);

/* Execute the Sphinx query. */
$sphinx = new SphinxClient();
$sphinx->SetServer(SPHINX_HOST, SPHINX_PORT);
$sphinx->SetWeights(array(0, 100, 0, 0, 50));
$sphinx->SetMatchMode(SPH_MATCH_BOOLEAN);
$sphinx->SetLimits(0, 10);
$sphinx->SetSortMode(SPH_SORT_TIME_SEGMENTS, 'date_added');
$sphinx->SetFilter('site_id', TEST_SITE_ID);

/* Execute the Sphinx query. Sphinx can ask us to retry if its
 * maxed out. Retry up to 5 times.
 */
$tries = 0;
do
{
    /* Wait for one second if this isn't out first attempt. */
    if (++$tries > 1)
    {
        sleep(1);
    }
    
    $results = $sphinx->Query(TEST_QUERY, SPHINX_INDEX);
    $errorMessage = $sphinx->GetLastError();
}
while (
    $results === false &&
    strpos($errorMessage, 'server maxed out, retry') !== false &&
    $tries <= 5
);

/* Throw a fatal error if Sphinx errors occurred. */
if ($results === false)
{
    fwrite($stderr, 'Sphinx Error: ' . ucfirst($errorMessage) . ".\n");
    exit(1);
}

/* Throw a fatal error (for now) if Sphinx warnings occurred. */
$lastWarning = $sphinx->GetLastWarning();
if (!empty($lastWarning))
{
    fwrite($stderr, 'Sphinx Warning: ' . ucfirst($lastWarning) . ".\n");
    exit(1);
}


fwrite($stdout, "Sphinx appears to be working properly.\n");
exit(0);

?>
