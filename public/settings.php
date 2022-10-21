<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

if ( !isset($_SESSION['user_id']) )
{
	exit_message('Not logged in', 'You must be logged in to view account settings');
}

$user_id = $_SESSION['user_id'];

// check if user is banned
require('inc/db.php');

// this will log the user out and exit if the user is banned
// if not, it will just return
checkBanned($db, $user_id);

$scripts[] = 'settings';

// set a csrf token
$csrf_token = new_csrf();

require('inc/tpl/header.php');
require('inc/tpl/settings.php');
require('inc/tpl/footer.php');

exit;

