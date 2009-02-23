<?php
//***********************************************************/
//*			            RUNCMS                              */
//*              Simplicity & ease off use                  */
//*             < http://www.runcms.org >                   */
//***********************************************************/

include_once('../../mainfile.php');

$files = unserialize(urldecode($_POST['files']));

if ($_POST['os'] == 'U') {
	$filename = 'chmod.sh';

$content  = '#!/bin/sh
#
# You can copy this file to your root install dir & call it from the shell
# prompt for easy chmodding if you have shell access on your server.
###########################################################################'."\n\n";

	foreach ($files as $key => $value) {
		$content .= 'chmod 0'.decoct($value).' '.$key.''."\n";
	}

	$content .= 'rm -r '.XOOPS_ROOT_PATH.'/_install';
	} else {
		$filename = 'chmod.bat';

$content  = "@echo You can copy this file to your root install dir & call it from the command\r\n@echo prompt for easy chmodding if you have shell access on your server.\r\n@echo off\r\npause\r\n@echo on\r\n";

		foreach ($files as $key => $value) {
			$key = str_replace('/', '\\', $key);
			if ($value == 0666 || $value == 0777) {
				$key = str_replace('/', '\\', $key);
				$content .= 'attrib -r '.$key.''."\r\n";
				} else {
					$content .= 'attrib +r '.$key.''."\r\n";
				}
		}
	$key = str_replace('/', '\\', XOOPS_ROOT_PATH);
	$content .= "@echo Will now delete the installation directory:\r\n@echo off\r\npause\r\n@echo on\r\n";
	$content .= 'deltree '.$key.'\_install';
	}

header('Cache-Control: no-store, no-cache, max-age=1, s-maxage=1, must-revalidate, post-check=0, pre-check=0');
header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$filename.'"');
print($content);
exit();
?>
