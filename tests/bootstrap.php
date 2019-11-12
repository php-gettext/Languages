<?php
error_reporting(E_ALL);

define('GETTEXT_LANGUAGES_TESTDIR', str_replace(DIRECTORY_SEPARATOR, '/', dirname(__FILE__)));

$cmd = escapeshellarg(defined('PHP_BINARY') ? PHP_BINARY : 'php');
$cmd .= ' ' . escapeshellarg(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'export-plural-rules');

$execOutput = array();
$rc = -1;
exec($cmd .' php ' . escapeshellarg('--output='.GETTEXT_LANGUAGES_TESTDIR.'/data.php'), $execOutput, $rc);
if ($rc !== 0) {
    throw new Exception(implode("\n", $execOutput));
}

exec($cmd .' json ' . escapeshellarg('--output='.GETTEXT_LANGUAGES_TESTDIR.'/data.json'), $execOutput, $rc);
if ($rc !== 0) {
    throw new Exception(implode("\n", $execOutput));
}

require_once dirname(dirname(__FILE__)).'/src/autoloader.php';
