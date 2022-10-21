<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

// check CSRF token
if ( !isset($_POST['csrf-token']) || ( check_csrf($_POST['csrf-token']) === false ) )
{
	exit_message('CSRF token mismatch', 'Sorry, there was an error with the form submission. Please go back and try again');
}

if ( !isset($_POST['passreset-submit']) )
{
	exit_message('Form submission error', 'The form was not submitted proeprly. Please go back and try again');
}

if ( !isset($_POST['email']) )
{
	exit_message('No email specified', 'Sorry, no email was specified. Please double check your email');
}

if ( !filter_var($_POST['email']) )
{
	exit_message('Invalid email', 'Sorry, this email address is invalid. Please double check your email');
}

$user_email = $_POST['email'];

// generate an auth code and update the DB
$user_passreset_verification = bin2hex(openssl_random_pseudo_bytes(16));

require('inc/db.php');

// update DB with code
$user_update = mysqli_prepare($db,
	'UPDATE `users`
		SET `user_passreset_verification` = ?, `user_passreset_expiry` = DATE_ADD(NOW(), INTERVAL 1 HOUR), `user_failed_logins` = 0
			WHERE `user_email` = ?'
);

mysqli_stmt_bind_param($user_update, 'ss', $user_passreset_verification, $user_email);
mysqli_stmt_execute($user_update);

if ( mysqli_stmt_affected_rows($user_update) === 0)
{
	exit_message('Password reset error', 'Sorry, a password request request could not be generated. Please double check your email address');
}

// send an email to the user
mail($user_email,
	SITE_NAME . ' - Your password reset link',
	'Hi!' .
	'<br /><br />' .
	'A request has been made to reset your password.' .
	'<br /><br />' .
	'Please <a href="' . SITE_URL . SCRIPT_PATH . 'newpass.php?email=' . $user_email . '&code=' . $user_passreset_verification . '">click here</a> to choose a new password.' .
	'<br /><br />' .
	'Thank you' .
	'<br /><br />' .
	SITE_NAME,
	array(
		'From' => EMAIL_FROM,
		'Reply-To' => EMAIL_FROM,
		'X-Mailer' => 'PHP/' . phpversion(),
		'Content-Type' => 'text/html; charset=UTF-8'
	)
);

exit_message('Password reset link sent', 'A password reset link has been sent to your email address');

