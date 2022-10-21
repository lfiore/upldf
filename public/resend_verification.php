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

// check CSRF token
if ( !isset($_GET['csrf-token']) || ( check_csrf($_GET['csrf-token']) === false ) )
{
	exit_message('CSRF token mismatch', 'Sorry, there was an error with the submission. Please go back and try again');
}

// check email
if ( !isset($_GET['email']) )
{
	exit_message('No email specified', 'Sorry, no email was specified. Please double check your email');
}

if ( !filter_var($_GET['email']) )
{
	exit_message('Invalid email', 'Sorry, this email address is invalid. Please double check your email');
}

// valid email
$user_email = $_GET['email'];

// check DB to see if accounts needs verifying and get verification code
require('inc/db.php');

$user_verification = mysqli_prepare($db,
	'SELECT UNIX_TIMESTAMP(`user_email_verification_sent`)
		FROM `users`
			WHERE `user_email` = ?
				AND `user_verified` = 0'
);


mysqli_stmt_bind_param($user_verification, 's', $user_email);
mysqli_stmt_execute($user_verification);
mysqli_stmt_store_result($user_verification);

if ( mysqli_stmt_num_rows($user_verification) === 0)
{
	exit_message('Unable to retrieve verification code', 'Either no user exists with this email address, or the account has already been verified');
}

mysqli_stmt_bind_result($user_verification, $user_email_verification_sent);
mysqli_stmt_fetch($user_verification);
mysqli_stmt_close($user_verification);

$elapsed_time = ( time() - $user_email_verification_sent );
$wait_time = ( 120 - $elapsed_time );

if ( $wait_time > 0 )
{
	exit_message('Please wait', 'Sorry, a verification email has recently been sent. Please wait another <span id="wait-time">' . $wait_time . '</span> seconds and try again', null, 'resend_verification');
}

// generate a new code and update DB
$user_email_verification = bin2hex(openssl_random_pseudo_bytes(16));

$user_update = mysqli_prepare($db,
	'UPDATE `users`
		SET `user_email_verification` = ?, `user_email_verification_sent` = NOW()
			WHERE `user_email` = ?'
);

mysqli_stmt_bind_param($user_update, 'ss', $user_email_verification, $user_email);
mysqli_stmt_execute($user_update);
mysqli_stmt_close($user_update);

// close DB connection
mysqli_close($db);

// email user with the verification link
mail($user_email,
	SITE_NAME . ' - Please verify your email',
	'Hi!' .
	'<br /><br />' .
	'Thank you for registering!' .
	'<br /><br />' .
	'This site requires you to verify your email address.' .
	'<br /><br />' .
	'Please <a href="' . SITE_URL . SCRIPT_PATH . 'verify.php?email=' . $user_email . '&code=' . $user_email_verification . '">click here</a> to verify your email address.' .
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

$verification_message = 'Another email has been sent. Please click on the link in your email to verify your email address';
$verification_error = 'No email? Check your spam folder or <a href="resend_verification.php?email=' . $user_email . '&csrf-token=' . new_csrf() . '">click here to get a new email</a>';

exit_message('Verification email sent', $verification_message, $verification_error);

