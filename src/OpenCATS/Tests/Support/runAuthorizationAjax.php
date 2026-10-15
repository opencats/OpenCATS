<?php

/* CLI fixture: execute real AJAX scripts, including their terminating errors. */
if (PHP_SAPI !== 'cli') exit(1);
$scenario = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$issues = array();
set_error_handler(function ($severity, $message, $file) use (&$issues) {
    // PHP 8.5 reports these pre-existing casts when compiling legacy includes.
    $legacyDeprecation = str_starts_with($message, 'Non-canonical cast') ||
        (str_ends_with($file, '/lib/DocumentToText.php') && str_starts_with($message, 'Case statements followed by a semicolon'));
    if ($severity !== E_DEPRECATED || !$legacyDeprecation)
    {
        $issues[] = $message;
    }
    return true;
});
require_once './config.php';
require_once './constants.php';
require_once './lib/DatabaseConnection.php';
require_once './lib/Session.php';
require_once './lib/Hooks.php';
require_once './lib/DataGrid.php';
require_once './lib/CATSUtility.php';
require_once './lib/DateUtility.php';

class AuthorizationFixtureDatabase extends DatabaseConnection
{
    public $scenario;
    public $writes = array();
    public $reads = array();

    public function __construct($scenario) { $this->scenario = $scenario; }
    public function makeQueryString($value) { return "'" . addslashes((string) $value) . "'"; }
    public function query($sql, $ignoreErrors = false) { $this->writes[] = $sql; return true; }
    public function getLastInsertID() { return 42; }
    public function getAssoc($sql = null)
    {
        $this->reads[] = $sql;
        if (preg_match('/FROM\s+activity\b/', $sql)) return $this->scenario['activity'] ?? array();
        if (preg_match('/FROM\s+candidate_joborder\b/', $sql))
        {
            if (str_contains($sql, 'isJobOrderAdminHidden')) return $this->scenario['pipeline'] ?? array();
            return array('ratingValue' => 3);
        }
        foreach (array('candidate', 'contact', 'company', 'joborder') as $table)
        {
            if (preg_match('/FROM\s+' . $table . '\b/', $sql)) return $this->scenario['parent'] ?? array();
        }
        return array();
    }
    public function getAllAssoc($sql = null)
    {
        $this->reads[] = $sql;
        return $this->scenario['rows'] ?? array();
    }
}

class ACL_SETUP
{
    public static $ACCESS_LEVEL_MAP = array();
}
ACL_SETUP::$ACCESS_LEVEL_MAP = array('' => $scenario['acl'] ?? array());
session_start();
$session = new CATSSession();
foreach (array('_isLoggedIn' => true, '_userID' => 10, '_accessLevel' => ($scenario['level'] ?? ACCESS_LEVEL_EDIT)) as $name => $value)
{
    (new ReflectionProperty(CATSSession::class, $name))->setValue($session, $value);
}
$_SESSION = array('CATS' => $session);
$db = new AuthorizationFixtureDatabase($scenario);
(new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $db);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('activityID' => '42', 'candidateJobOrderID' => '42', 'rating' => '3',
    'type' => '400', 'jobOrderID' => 'NULL', 'notes' => 'Updated by a colleague',
    'date' => '10-02-26', 'hour' => '01', 'minute' => '00', 'ampm' => 'PM',
    'csrfToken' => $session->getCSRFToken());
$_REQUEST = $_POST;
ob_start();
register_shutdown_function(function () use ($db, &$issues) {
    $output = ob_get_clean();
    session_destroy();
    echo json_encode(array('output' => $output, 'writes' => $db->writes, 'reads' => $db->reads,
        'issues' => $issues, 'fatal' => error_get_last()), JSON_THROW_ON_ERROR);
});
$endpoint = $scenario['endpoint'];
if (!in_array($endpoint, array('editActivity', 'deleteActivity', 'setCandidateJobOrderRating', 'getPipelineDetails'), true))
{
    throw new InvalidArgumentException('Unsupported test endpoint.');
}
require './ajax/' . $endpoint . '.php';
