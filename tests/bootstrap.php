<?php
error_reporting(E_ALL);

date_default_timezone_set('UTC');

exec('php '.dirname(dirname(__FILE__)).'/convert.php php > '.dirname(__FILE__).'/data.php');
