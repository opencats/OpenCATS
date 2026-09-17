<?php

/*
 * CLI-only presentation fixture, adapted from the Reports isolated smoke check.
 * Uses the real Template class and report templates, but substitutes shell helpers
 * in a separate process. This does not test application routing or fix #885:
 * https://github.com/opencats/OpenCATS/issues/885
 */
if (PHP_SAPI !== 'cli')
{
    http_response_code(404);
    exit;
}

error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$templates = array('Submissions' => 'SubmissionReport.tpl', 'Placements' => 'PlacedReport.tpl');
if ($argc !== 4 || !isset($templates[$argv[1]]) || !in_array($argv[3], array('empty', 'populated'), true))
{
    throw new InvalidArgumentException('Expected report category, title and empty/populated results.');
}

chdir(dirname(__DIR__, 2));
define('HTML_ENCODING', 'UTF-8');
require 'lib/Template.php';

// Explicit shell-only double: unexpected template helper calls still fail.
class TemplateUtility
{
    public static function printHeader($title)
    {
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>',
            Template::escapeHtml($title), '</title></head><body>';
    }

    public static function printHeaderBlock() {}

    public static function printReportFooter()
    {
        echo '</body></html>';
    }
}

$rows = array(
    array('firstName' => 'Reports', 'lastName' => 'Fixture', 'ownerFullName' => 'Owner & Team', 'dateSubmitted' => '03-02-20 (12:00 PM)'),
    array('firstName' => 'Second', 'lastName' => 'Candidate', 'ownerFullName' => 'Another Owner', 'dateSubmitted' => '04-02-20 (01:30 PM)')
);
$jobs = $argv[3] === 'empty' ? array() : array(array(
    'title' => 'Fixture Job', 'companyName' => 'Fixture Company', 'ownerFullName' => 'Fixture Owner',
    'submissionsRS' => $rows, 'placementsRS' => $rows
));

$template = new Template();
$template->assign('reportTitle', $argv[2]);
$template->assign('submissionJobOrdersRS', $jobs);
$template->assign('placementsJobOrdersRS', $jobs);
$template->display('modules/reports/' . $templates[$argv[1]]);
