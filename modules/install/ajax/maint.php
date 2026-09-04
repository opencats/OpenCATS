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
