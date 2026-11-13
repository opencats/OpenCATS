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
    private $backupRows = array();

    public function __construct($scenario) { $this->scenario = $scenario; }
    public function makeQueryString($value) { return "'" . addslashes((string) $value) . "'"; }
    public function query($sql, $ignoreErrors = false)
    {
        if (($this->scenario['endpoint'] ?? '') === 'backup' && str_starts_with($sql, 'SELECT'))
        {
            $this->reads[] = $sql;
            $this->backupRows = $this->scenario['rows'];
            if (preg_match('/attachment_id <> (\d+)/', $sql, $matches))
            {
                $this->backupRows = array_values(array_filter($this->backupRows,
                    fn($row) => (string) $row['attachment_id'] !== $matches[1]));
            }
        }
        else
        {
            $this->writes[] = $sql;
        }
        return true;
    }
    public function getNumRows($query = null) { return count($this->backupRows); }
    public function getLastInsertID() { return 42; }
    public function getAssoc($sql = null)
    {
        $this->reads[] = $sql;
        if (($this->scenario['endpoint'] ?? '') === 'backup')
        {
            return $sql === null ? (array) array_shift($this->backupRows) : $this->scenario['backup'];
        }
        if (preg_match('/FROM\s+attachment\b/', $sql)) return $this->scenario['attachment'] ?? array();
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
foreach (array('_isLoggedIn' => ($scenario['loggedIn'] ?? true), '_userID' => 10, '_accessLevel' => ($scenario['level'] ?? ACCESS_LEVEL_EDIT)) as $name => $value)
{
    (new ReflectionProperty(CATSSession::class, $name))->setValue($session, $value);
}
$_SESSION = array('CATS' => $session);
$db = new AuthorizationFixtureDatabase($scenario);
(new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $db);
$_SERVER['REQUEST_METHOD'] = $scenario['method'] ?? 'POST';
$_POST = array('activityID' => '42', 'candidateJobOrderID' => '42', 'rating' => '3',
    'type' => '400', 'jobOrderID' => 'NULL', 'notes' => 'Updated by a colleague',
    'date' => '10-02-26', 'hour' => '01', 'minute' => '00', 'ampm' => 'PM',
    'csrfToken' => ($scenario['validToken'] ?? true) ? $session->getCSRFToken() : 'invalid');
$_REQUEST = array_replace($_POST, $scenario['request'] ?? array());
ob_start();
register_shutdown_function(function () use ($db, &$issues) {
    $output = ob_get_clean();
    if (session_status() === PHP_SESSION_ACTIVE)
    {
        session_destroy();
    }
    echo json_encode(array('output' => $output, 'writes' => $db->writes, 'reads' => $db->reads,
        'issues' => $issues, 'fatal' => error_get_last()), JSON_THROW_ON_ERROR);
});
$endpoint = $scenario['endpoint'];
if ($endpoint === 'backup')
{
    (new ReflectionProperty(CATSSession::class, '_siteCompanyID'))->setValue($session, 7);
    $session->storeValueByName('backupDirectory', './attachments/archive/');
    $_POST['attachmentID'] = '42';
    $_POST['a'] = 'backup';
    $_POST['attachmentsOnly'] = 'true';
    $script = getcwd() . '/modules/settings/ajax/backup.php';
    chdir($scenario['fixture']);
    require $script;
    return;
}
if (in_array($endpoint, array('getAttachment', 'deleteBackup'), true))
{
    require_once './lib/UserInterface.php';
    require_once './lib/Template.php';
    require_once './modules/attachments/AttachmentsUI.php';
    require_once './modules/settings/SettingsUI.php';
    (new ReflectionProperty(CATSSession::class, '_siteCompanyID'))->setValue($session, 7);
    $_SERVER['HTTP_HOST'] = 'example.test';
    $_SERVER['PHP_SELF'] = '/index.php';
    $_GET = array('a' => $endpoint, 'id' => '42',
        'directoryNameHash' => md5($scenario['attachment']['directoryName']));
    $_POST['postback'] = 'postback';
    chdir($scenario['fixture']);

    $ui = $endpoint === 'getAttachment' ? new AttachmentsUI() : new SettingsUI();
    $ui->handleRequest();
    return;
}
if ($endpoint === 'processMassImportItem')
{
    $_SESSION['CATS_LEGACY_BULK_RESUMES'] = $scenario['queue'];
    $script = getcwd() . '/modules/import/ajax/processMassImportItem.php';
    chdir($scenario['fixture']);
    require $script;
    return;
}
if (!in_array($endpoint, array('editActivity', 'deleteActivity', 'setCandidateJobOrderRating', 'getPipelineDetails', 'getPipelineJobOrder'), true))
{
    throw new InvalidArgumentException('Unsupported test endpoint.');
}
require './ajax/' . $endpoint . '.php';
