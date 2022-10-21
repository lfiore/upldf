<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

if ( isset($_SESSION['user_id'] ) )
{
	session_unset();
	session_destroy();

	exit_message('Logged out', 'You have now been logged out');
}
else
{
	exit_message('Not logged in', 'You are not currently logged in');
}

