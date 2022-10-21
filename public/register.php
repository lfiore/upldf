<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

// check if user is logged in
if ( isset($_SESSION['user_id']) )
{
	exit_message('Already logged in', 'You are already logged in');
}

if ( ALLOW_NEW_USERS )
{

	require('inc/tpl/header.php');
	require('inc/tpl/register.php');
	require('inc/tpl/footer.php');

}
else
{
	exit_message('New registrations are disabled', 'Sorry, new registrations have been disabled');
}

exit;