<?php

// check if file was accessed directly and exit if so
if ( !defined('IN_APP') )
{
	exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try
{
	$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME, DB_PORT);
}
catch (Exception $error)
{
	exit_message('Unable to connect to DB', 'Unable to connect to the database server');
}

