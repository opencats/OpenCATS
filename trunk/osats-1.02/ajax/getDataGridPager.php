<?php
/*
   * OSATS
   *
   *
   * Open Source GNU License will apply
*/

include_once('./lib/osatutil.php');
include_once('./lib/TemplateUtility.php');
include_once('./lib/DataGrid.php');

$interface = new SecureAJAXInterface();

if (!isset($_REQUEST['p']) ||
    !isset($_REQUEST['i']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid input.');
    die();
}

$indentifier = $_REQUEST['i'];
$parameters = unserialize($_REQUEST['p']);

/* Handle dynamicArgument if it is set. */
if (isset($_REQUEST['dynamicArgument']))
{
    foreach ($parameters as $index => $data)
    {
        if ($data === '<dynamic>')
        {
            $parameters[$index] = $_REQUEST['dynamicArgument'];
        }
    }
}

$dataGrid = DataGrid::get($indentifier, $parameters);

$dataGrid->draw(true);
$dataGrid->drawUpdatedNavigation(true);
