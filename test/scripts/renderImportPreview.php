<?php
/**
 * CLI-only presentation fixtures for states normally supplied by the resume parser.
 * No database, upload queue, external parser, or import processing is invoked.
 * Usage: php test/scripts/renderImportPreview.php edit > /tmp/import-edit.html
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(dirname(__DIR__, 2));
require_once 'lib/Template.php';
class CATSUtility { public static function getIndexName() { return 'index.php'; } }
class TemplateUtility
{
    public static function printHeader(...$args) {}
    public static function printHeaderBlock() {}
    public static function printTabs(...$args) {}
    public static function printQuickSearch() {}
    public static function printFooter() {}
    public static function printModalHeader(...$args) {}
    public static function getVersionedAssetURL($path) { return $path; }
}
$view = $argv[1] ?? 'edit';
$views = array('queue' => 1, 'empty' => 1, 'progress' => 2, 'review' => 3, 'finish' => 4);
if (!isset($views[$view]) && !in_array($view, array('edit', 'error', 'modal', 'recent', 'bulk', 'landing'), true)) exit(1);
define('ACCESS_LEVEL_SA', 500);
$_SESSION['CATS'] = new class { public function getAccessLevel($name) { return ACCESS_LEVEL_SA; } };
$template = new Template();
$template->assign('active', null);
$template->assign('step', $views[$view] ?? 3);
$template->assign('multipleFilesEnabled', true);
$template->assign('uploadPath', '/example/upload');
$doc = array('id' => 0, 'name' => 'test/data/import-small.csv', 'realName' => 'Import-preview-resume.txt',
    'contents' => "ImportPreview Fixture\nExample City\nTesting and documentation\n", 'firstName' => 'ImportPreview',
    'lastName' => 'Fixture', 'email' => 'preview@example.test', 'city' => 'Example City', 'state' => '', 'zipCode' => '');
$template->assign('document', $doc);
$template->assign('documentID', 0);
if ($view !== 'empty') $template->assign('documents', array($doc));
$template->assign('files', array($doc, $doc));
$template->assign('js', '');
$template->assign('importedCandidates', array(array('name' => 'ImportPreview Fixture', 'url' => '?m=candidates&a=show&candidateID=20000', 'location' => 'Example City')));
$template->assign('importedDocuments', array(array('name' => 'unclassified.txt')));
$template->assign('importedFailed', array(array('name' => 'failed.txt')));
$template->assign('importedDuplicates', array($doc));
$template->assign('errorMessage', 'Import preview error.');
$template->assign('data', array(array('importID' => 42, 'dateCreated' => '19 September 2026', 'addedLines' => 1)));
$template->assign('importErrors', 'Line 2: focused import error');
$template->assign('importID', 42);
$template->assign('foundFiles', array('preview.txt'));
$template->assign('bulk', array('numBulkAttachments' => 2));
if (in_array($view, array('recent', 'landing'), true))
{
    unset($template->errorMessage);
    $template->assign('successMessage', 'The revert was successful.');
}

// Normal wizard states must not inherit the error branch.
if (isset($views[$view])) unset($template->errorMessage);
ob_start();
if (isset($views[$view]))
{
    $template->display('modules/import/MassImportStep' . $views[$view] . '.tpl');
    $template->assign('subTemplateContents', ob_get_clean());
    ob_start();
    $template->display('modules/import/MassImport.tpl');
}
else $template->display('modules/import/' . array('edit' => 'MassImportEdit', 'error' => 'Error', 'modal' => 'ErrorModal', 'recent' => 'ImportRecent', 'bulk' => 'ImportResumesBulk', 'landing' => 'Import1')[$view] . '.tpl');
$html = ob_get_clean();
// Preserve script includes so dependency regressions cannot be hidden by this fixture.
$html = preg_replace('~<link[^>]+>|</?body[^>]*>|</html>~i', '', $html);
echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><style>',
    file_get_contents('vendor/twbs/bootstrap/dist/css/bootstrap.min.css'), "\n",
    file_get_contents('modules/import/MassImport.css'), '</style><script>',
    'window.importPreviewErrors = []; window.addEventListener("error", function (event) { window.importPreviewErrors.push(event.message); });',
    'function getObj(id) { return document.getElementById(id); }',
    file_get_contents('js/massImport.js'),
    file_get_contents('modules/import/import.js'),
    'function initPopUp() {}',
    // Processing is deliberately stopped at the rendering boundary in this fixture.
    'function startDocumentParsing() {}', '</script></head><body>', $html, '</body></html>';
