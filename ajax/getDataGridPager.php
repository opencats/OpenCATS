<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

include_once(__DIR__ . '/bootstrap.php');

include_once(LEGACY_ROOT . '/lib/CATSUtility.php');
include_once(LEGACY_ROOT . '/lib/TemplateUtility.php');
include_once(LEGACY_ROOT . '/lib/DataGrid.php');

$interface = new SecureAJAXInterface();

if (!isset($_REQUEST['p']) ||
    !isset($_REQUEST['i']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid input.');
    die();
}

$indentifier = $_REQUEST['i'];
$parameters = json_decode($_REQUEST['p'],true);

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

?>
