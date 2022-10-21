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

// check if this is a request for a new code or validation attempt
if ( !isset($_GET['email']) )
{
	exit_message('No email specified', 'Sorry, no email was specified. Please double check the link in your email');
}

if ( !filter_var($_GET['email']) )
{
	exit_message('Invalid email', 'Sorry, this email address is invalid. Please double check the link in your email');
}

if ( !isset($_GET['code']) )
{
	exit_message('No verification code specified', 'Sorry, no verification code was specified. Please double check the link in your email');
}

require('inc/tpl/header.php');
require('inc/tpl/newpass.php');
require('inc/tpl/footer.php');

exit;

