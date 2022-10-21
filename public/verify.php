<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

if ( !isset($_GET['email']) || !filter_var($_GET['email']) )
{
	exit_message('Invalid email', 'Sorry, this email address is invalid. Please double check the link in your email');
}

$user_email = $_GET['email'];

if ( !isset($_GET['code']) )
{
	exit_message('No code specified', 'Sorry, no authentication code was specified. Please double check the link in your email');
}

$user_email_verification = $_GET['code'];

// attempt to verify account
require('inc/db.php');

$user_verify = mysqli_prepare($db,
	'UPDATE `users`
		SET `user_verified` = 1
			WHERE `user_email` = ?
				AND `user_email_verification` = ?'
);

mysqli_stmt_bind_param($user_verify, 'ss', $user_email, $user_email_verification);
mysqli_stmt_execute($user_verify);

if ( mysqli_stmt_affected_rows($user_verify) === 1)
{
	exit_message('Email verified', 'Your email address has been verified and you can now log in');
}

else
{
	exit_message('Unable to verify email', 'Sorry, unable to verify your email account. It may already be verified or the code may be incorrect');
}

