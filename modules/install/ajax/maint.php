<?php
/*
 * CATS
 * Installation Maintenance Interface
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
 * $Id: maint.php 3346 2007-10-29 22:40:53Z brian $
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: POST');
    die();
}

$installerActive = !file_exists('INSTALL_BLOCK');

if (!$installerActive)
{
    /* This endpoint is reached through ajax.php, which loads the required
     * dependencies, starts the session and validates the CSRF token. A fresh
     * installation uses the installer's existing access model. An installed
     * system additionally requires an authenticated site admin.
     */
    if (!isset($_SESSION['CATS']) ||
        !$_SESSION['CATS']->isLoggedIn() ||
        $_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_SA)
    {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied.');
    }
}

if (file_exists('./modules.cache'))
{
    @unlink('./modules.cache');
}

if (isset($_SESSION['modules']))
{
    unset($_SESSION['modules']);
}

$maintPage = true;

include_once('index.php');
