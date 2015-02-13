<?php
error_reporting(E_ALL);

$execOutput = array();
exec('php '.dirname(dirname(__FILE__)).'/bin/convert.php php --output='.dirname(__FILE__).'/data.php', $execOutput, $rc);
if($rc !== 0) {
    throw new Exception(implode("\n", $execOutput));
}
