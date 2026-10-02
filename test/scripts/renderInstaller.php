<?php
/* CLI-only presentation fixtures. Never runs installation or changes application data. */
if (PHP_SAPI !== 'cli')
{
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__, 2);
chdir($root);
define('LEGACY_ROOT', $root);
$_SERVER['SERVER_SOFTWARE'] = 'Installer presentation fixture';
$kind = $argv[1] ?? 'shell';

if ($kind === 'extras')
{
    // Evaluate only the output-building tail, never the database detection or install code.
    // This exercises the nested PHP/JavaScript quoting with a representative component.
    $source = file_get_contents($root . '/modules/install/ajax/ui.php');
    $start = strpos($source, "        \$onClick  =");
    $end = strpos($source, "    case 'setupOptional':", $start);
    if ($start === false || $end === false)
    {
        throw new RuntimeException('Optional component renderer not found.');
    }
    $optionalComponents = array('example' => array(
        'name' => 'Example feature', 'description' => 'Optional component details.', 'componentExists' => false
    ));
    eval('switch (true) { default: ' . substr($source, $start, $end - $start) . ' }');
    exit;
}
if ($kind === 'shell')
{
    require 'installwizard.php';
    exit;
}
if ($kind === 'notinstalled')
{
    require 'modules/install/notinstalled.php';
    exit;
}
if ($kind === 'unsupported')
{
    // Deliberately do not load config, TemplateUtility, sessions or the database.
    $minimumPHPVersion = '8.4.1';
    require 'modules/install/phpVersion.php';
    exit;
}
if (in_array($kind, array('locked', 'start', 'checks', 'missing-database'), true))
{
    // Isolate INSTALL_BLOCK and any filesystem probes from the working installation.
    $temporary = sys_get_temp_dir() . '/opencats-installer-' . bin2hex(random_bytes(8));
    mkdir($temporary);
    chdir($temporary);
    register_shutdown_function(static function () use ($temporary, $root) {
        foreach (glob($temporary . '/*') as $file)
        {
            if (is_file($file)) unlink($file);
        }
        chdir($root);
        rmdir($temporary);
    });
    if ($kind === 'locked') file_put_contents('INSTALL_BLOCK', 'Presentation test');
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = array('a' => match ($kind) {
        'checks' => 'installTest',
        'missing-database' => 'testDatabaseConnectivity',
        default => 'startInstall'
    });
    if ($kind === 'missing-database')
    {
        $_POST += array('user' => '', 'pass' => '', 'host' => '', 'name' => '');
    }
    require $root . '/modules/install/ajax/ui.php';
    exit;
}

require 'config.php';
require 'constants.php';
require 'lib/Template.php';
require 'lib/TemplateUtility.php';
require 'lib/CATSUtility.php';
$template = new Template();
if (in_array($kind, array('pending-admin', 'pending-user'), true))
{
    $template->assign('isAdministrator', $kind === 'pending-admin');
    $template->display('modules/login/PendingMigrations.tpl');
    exit;
}
if (in_array($kind, array('password', 'localization', 'siteName', 'text', 'conclusion'), true))
{
    $template->assign('message', '');
    $template->assign('title', 'Initial Configuration Wizard');
    $template->assign('prompt', 'Configure your OpenCATS installation.');
    $template->assign('inputType', $kind);
    $template->assign('inputTypeTextParam', 'Site name');
    $template->assign('action', 'newInstallWizard');
    $template->assign('home', 'home');
    $template->display('modules/settings/NewInstallWizard.tpl');
    exit;
}
throw new InvalidArgumentException('Unknown installer fixture.');
