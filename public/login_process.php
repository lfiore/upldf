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
if ( !isset($_POST['csrf-token']) || ( check_csrf($_POST['csrf-token']) === false ) )
{
	exit_message('CSRF token mismatch', 'Sorry, there was an error with the form submission. Please go back and try again');
}

$errors = [];

if ( !isset($_POST['login-submit']) )
{
	exit_message('Form submission error', 'The registration form was not submitted proeprly. Please go back to the registration page and try again');
}

// check if email field has been filled in
if ( empty($_POST['email']) )
{
	// no email set
	$errors[] = 'Email address not filled in';
}
else
{
	// check if email is valid
	if ( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) )
	{
		// invalid email
		$errors[] = 'Invalid email address entered';
	}
	else
	{
		// valid email
		$user_email = $_POST['email'];
	}
}

// check if password field has been filled in
if ( empty($_POST['password']) )
{
	// password field not filled in
	$errors[] = 'Password field not filled in';
}
else
{
	$raw_user_password_peppered = $_POST['password'] . USER_PEPPER;
}

// if there are any errors set, let the user know
if ( !empty($errors) )
{
	exit_message('Login error', 'The following errors were encountered during login. Please go back and try again.', $errors);
	exit;
}

// no errors, continue with login
// check the database to see if a user account exists with that email
// obtain verified, banned and admin status

// open DB connection
require('inc/db.php');

$user_get = mysqli_prepare($db,
	'SELECT `user_id`, `user_password`, `user_verified`, `user_failed_logins`, UNIX_TIMESTAMP(`user_last_failed_login`), `user_banned`, `user_closed`, `user_admin`
		FROM `users`
			WHERE `user_email` = ?
');

mysqli_stmt_bind_param($user_get, 's', $user_email);
mysqli_stmt_bind_result($user_get, $user_id, $user_password, $user_verified, $user_failed_logins, $user_last_failed_login, $user_banned, $user_closed, $user_admin);

mysqli_stmt_execute($user_get);
mysqli_stmt_fetch($user_get);
mysqli_stmt_close($user_get);

// check to see if a row was returned from the DB
if ( !isset($user_id) )
{
	exit_message('Login error', 'Sorry, no account exists with this email address');
}

// check for banned/closed/unverified account
if ( $user_banned === 1 )
{
	exit_message('User banned', 'Sorry, this account has been banned');
}

if ( $user_closed === 1 )
{
	exit_message('Account closed', 'Sorry, this account has been closed');
}

if ( VERIFY_EMAIL && ( $user_verified === 0 ) )
{
	$verification_error = 'No email? Check your spam folder or <a href="resend_verification.php?email=' . $user_email . '&csrf-token=' . new_csrf() . '">click here to get a new email</a>';
	exit_message('Email not verified', 'Sorry, this email address has not been verified. Please verify it by clicking on the link in your email', $verification_error);
}

// check last failed login time against lockout time
$user_lockout_expiry = ( ( $user_last_failed_login + LOCKOUT_TIME ) - time() );

// see if user has failed a login attempt recently (in the time specified by LOCKOUT_TIME)
if ( $user_lockout_expiry >= 0 )
{
	// see if user has reached the specified number of attempts required before the account is locked out
	if ( $user_failed_logins >= LOCKOUT_MAX_ATTEMPTS )
	{
		exit_message('Login error', 'Sorry, your account has now been locked out. You can try again in another ' . $user_lockout_expiry . ' seconds or reset your password from the login page.');
	}
}
else
{
	// user hasn't failed a login attempt in the lockout time period, so reset to 0
	$user_failed_logins = 0;
}

// account isn't locked out

// check password
if ( !password_verify($raw_user_password_peppered, $user_password) )
{

	// incorrect password
	// increase failed_logins and update user_last_failed_login
	$user_new_failed_logins = ++$user_failed_logins;

	if ($user_new_failed_logins >= LOCKOUT_MAX_ATTEMPTS)
	{
		$failed_login_message = 'Your account has now been locked out for ' . LOCKOUT_TIME . ' seconds';
	}
	else
	{
		$user_remaining_attempts = ( LOCKOUT_MAX_ATTEMPTS - $user_new_failed_logins );
		$failed_login_message = 'You have ' . $user_remaining_attempts . ' attempts left before your account is locked out for ' . LOCKOUT_TIME . ' seconds';
	}

	// update database with failed login info
	$update_failed_login = mysqli_prepare($db,
		'UPDATE `users`
			SET `user_failed_logins` = ?, `user_last_failed_login` = NOW()
				WHERE `user_email` = ?'
	);

	mysqli_stmt_bind_param($update_failed_login, 'is', $user_new_failed_logins, $user_email);
	mysqli_stmt_execute($update_failed_login);
	mysqli_stmt_close($update_failed_login);

	exit_message('Login error', 'Sorry, your password is incorrect. ' . $failed_login_message);

}

// password is correct

// reset failed_logins if applicable
if ( $user_failed_logins !== 0 )
{
	$update_successful_login = mysqli_prepare($db,
		'UPDATE `users`
			SET `user_failed_logins` = ?
				WHERE `user_email` = ?'
	);

	$user_failed_logins = 0;
	mysqli_stmt_bind_param($update_successful_login, 'is', $user_failed_logins, $user_email);
	mysqli_stmt_execute($update_successful_login);
	mysqli_stmt_close($update_successful_login);
}

// close DB connection
mysqli_close($db);

// set user's status as logged in
$_SESSION['user_id'] = $user_id;
$_SESSION['user_email'] = $user_email;

// set the user's status as an admin (if it's an admin account)
if ( $user_admin === 1 )
{
	$_SESSION['user_admin'] = true;
}

exit_message('Login complete', 'Thank you, you may now access your account');

?>