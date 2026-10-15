<?php

// Behat runs beside PHP-FPM in CI and can read its native session store.
// Never expose session contents through an HTTP request.
if (PHP_SAPI !== 'cli')
{
    http_response_code(404);
    exit;
}

$sessionID = $argv[1] ?? '';
if (!preg_match('/^[a-zA-Z0-9,-]+$/D', $sessionID))
{
    fwrite(STDERR, "Invalid browser session ID.\n");
    exit(1);
}

// CI uses PHP's file-backed sessions. Read without opening the browser's
// www-data-owned file for writing (which protected_regular can reject for CLI).
$savePath = ini_get('session.save_path');
if (ini_get('session.save_handler') !== 'files' || str_contains($savePath, ';'))
{
    fwrite(STDERR, "This Behat helper requires the CI file-backed session configuration.\n");
    exit(1);
}
$sessionFile = ($savePath !== '' ? $savePath : sys_get_temp_dir()) . '/sess_' . $sessionID;
$data = file_get_contents($sessionFile);
if ($data === false)
{
    fwrite(STDERR, "Behat must share PHP-FPM's session save path.\n");
    exit(1);
}

// Decode with PHP itself in an unrelated, temporary CLI session. Never write
// to the browser's session, expose the phrase over HTTP, or replace Behat state.
session_start(array('use_cookies' => false, 'cache_limiter' => ''));
$decoded = session_decode($data);
$state = array(
    'captcha' => $_SESSION['careerPortalCaptcha'] ?? null,
    'candidateID' => $_SESSION['careerPortalCandidateID'] ?? null
);
session_destroy();
if (!$decoded)
{
    fwrite(STDERR, "Unable to decode the browser session.\n");
    exit(1);
}
echo json_encode($state, JSON_THROW_ON_ERROR);
