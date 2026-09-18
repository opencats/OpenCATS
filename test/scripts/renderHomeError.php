<?php

/* CLI-only Home presentation fixture. Shell and hook doubles stay in this process. */
if (PHP_SAPI !== 'cli')
{
    http_response_code(404);
    exit;
}

chdir(dirname(__DIR__, 2));
define('HTML_ENCODING', 'UTF-8');
require 'lib/Template.php';

class TemplateUtility
{
    public static function printHeader($title) { echo '<html><head><title>' . $title . '</title></head><body>'; }
    public static function printHeaderBlock() { echo '<header>Application header</header>'; }
    public static function printTabs($active) { echo '<nav>Application tabs</nav>'; }
    public static function printQuickSearch() { echo '<form>Quick Search</form>'; }
    public static function printFooter() { echo '<footer>Application footer</footer></body></html>'; }
}

class Hooks
{
    public static function get($name)
    {
        if ($name !== 'FRIENDLYERRORS_CONTACTCATS')
        {
            throw new RuntimeException('Unexpected hook: ' . $name);
        }
        return 'echo "Home error hook output";';
    }
}

$kind = $argv[1] ?? '';
if (!in_array($kind, array('fatal', 'page', 'modal', 'demo'), true))
{
    throw new InvalidArgumentException('Expected fatal, page, modal or demo.');
}
$template = new Template();
$template->assign('active', null);
$template->assign('modal', $kind === 'modal');
$template->assign('isDemo', $kind === 'demo');
$template->assign('errorTitle', 'Home fixture error');
$template->assign('errorMessage', 'Useful details <a href="?m=home">Return home</a>');
$template->display('modules/home/' . ($kind === 'fatal' ? 'Error.tpl' : 'FriendlyError.tpl'));
