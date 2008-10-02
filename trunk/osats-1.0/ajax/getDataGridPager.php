<?php
/*
 * CATS
 * AJAX Pager interface
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: getDataGridPager.php 3078 2007-09-21 20:25:28Z will $
 */

include_once('./lib/CATSUtility.php');
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

?>