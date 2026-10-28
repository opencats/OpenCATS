<?php
/** CLI-only visual fixture. Real reopening template; no database connection or writes. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(dirname(__DIR__,2));
define('LEGACY_ROOT','.');
define('HTML_ENCODING','UTF-8');
require 'lib/Template.php';
class CATSUtility { public static function getIndexName() { return 'index.php'; } }
class TemplateUtility
{
    public static function printModalHeader($title,$includes=array(),$heading='')
    {
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OpenCATS reopening fixture</title><link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css"><link rel="stylesheet" href="main.css"><script>CATSIndexName="index.php";</script>';
        foreach ($includes as $file) echo '<script defer src="'.Template::escapeAttr($file).'"></script>';
        echo '</head><body class="bg-body-tertiary"><p class="small p-2">Synthetic reopening fixture — no database changes</p>';
    }
    public static function printBootstrapBundle() { echo '<script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>'; }
    public static function printFooter() { echo '</body></html>'; }
}
$_SESSION['CATS']=new class { public function getCSRFToken() { return 'synthetic-token'; } };
$status=$argv[1] ?? 'Full';
if (!in_array($status,array('Full','Closed'),true)) throw new InvalidArgumentException('Invalid fixture status.');
$template=new Template();
$template->assign('reopenJob',array('status'=>$status,'jobOrderID'=>1,'reopenVersion'=>str_repeat('a',64)));
$template->display('modules/joborders/ReopenModal.tpl');
