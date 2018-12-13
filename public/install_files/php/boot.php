<?php

if (version_compare(PHP_VERSION, '5.6.4', '<')) exit('You need at least PHP 5.6.4 to install this application.');

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

@set_time_limit(3600);
ini_set('pcre.recursion_limit', '524'); // 256KB stack. Win32 Apache

require_once 'Installer.php';

try {
	$installer = new Installer();
	$checks = $installer->checkForIssues();
}
catch (Exception $e) {
	echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
	exit;
}