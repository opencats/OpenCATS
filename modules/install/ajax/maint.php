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
    header('Content-Type: text/html; charset=UTF-8');

    $actionURL = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');

    echo '<!DOCTYPE html>',
         '<html><head><title>OpenCATS Maintenance</title></head><body>',
         '<p>This maintenance action must be triggered via POST.</p>',
         '<p>This page starts maintenance mode and related installer tasks.</p>',
         '<form method="post" action="', $actionURL, '">',
         '<input type="hidden" name="postback" value="postback" />',
         '<button type="submit">Run maintenance now</button>',
         '</form>',
         '</body></html>';
    die();
}

if (file_exists('./modules.cache'))
{
    @unlink('./modules.cache');
}

$maintPage = true;

include_once('index.php');
