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

// form submission checks
// these fields are all hidden so shouldn't fail here unless the form was modified
if ( !isset($_POST['newpass-submit']) || empty($_POST['email']) || empty($_POST['verification-code']) )
{
	exit_message('Form submission error', 'The new password form was not submitted proeprly. Please go back to the new password page and try again');
}

// decode email first
$user_email_decoded = html_entity_decode($_POST['email'], ENT_QUOTES);

// check if email is valid
// it shouldn't be since it's in a hidden input field
if ( !filter_var($user_email_decoded, FILTER_VALIDATE_EMAIL) )
{
	exit_message('Invalid email', 'The email is invalid. Please follow the instructions in your email again');
}

// valid email
$user_email = $_POST['email'];

// check if verificaiton-code is valid (alphanumberic) to possibly avoid a DB query
// it shouldn't be since it's in a hidden input field
if ( !ctype_alnum($_POST['verification-code']) )
{
	exit_message('Invalid verification code', 'The verification code is invalid. Please follow the instructions in your email again');
}

// valid verification code
$user_passreset_verification = $_POST['verification-code'];

// set errors array
$errors = [];

// check if password confirmation field has been filled in
if ( empty($_POST['password-confirmation']) )
{
	// no password confirmation set
	$errors[] = 'Password confirmation not filled in';
}
else
{
	// check if password confirmaiton matches previous password
	if ( $_POST['password'] !== $_POST['password-confirmation'] )
	{
		// passwords don't match
		$errors[] = 'Password and confirmation password do not match';
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
	// password field has been filled in
	// check complexity requirements
	// check password length (set in config.php)
	if ( strlen($_POST['password']) < PASSWORD_MIN )
	{
		// password is too short
		$errors[] = 'Password should be at least ' . PASSWORD_MIN . ' characters long';
	}

	// see if password complexiy is required in config.php
	if  ( PASSWORD_COMPLEX )
	{
		// check if password is different to the email
		if ( $_POST['password'] === $_POST['email'])
		{
			$errors[] = 'Your password cannot be the same as your email';
		}

		// check password complexity
		if ( ctype_alpha($_POST['password']) )
		{
			// password is letters only
			$errors[] = 'Your password should contain at least 1 number and special character';
		}
		else
		{
			// password is not just letters
			if ( ctype_alnum($_POST['password']) )
			{
				// password is just letters and numbers
				$errors[] = 'Your password should contain at least 1 special character';
			}
			else
			{
				// password meets requirements
				$user_raw_password = $_POST['password'];
			}
		}
	}
	else
	{
		// password meets requirements
		$user_raw_password = $_POST['password'];
	}

}

// if there are any errors set, let the user know
if ( !empty($errors) )
{
	exit_message('Password reset error', 'The following errors were encountered during your password reset. Please go back and try again.', $errors);
	exit;
}

// no errors, continue with password reset
// check DB for verification code

// open DB connection
require('inc/db.php');

$user_verify = mysqli_prepare($db,
	'SELECT COUNT(`user_id`)
		FROM `users`
			WHERE `user_email` = ?
				AND `user_passreset_verification` = ?
					AND `user_passreset_expiry` > NOW()'
);

mysqli_stmt_bind_param($user_verify, 'ss', $user_email, $user_passreset_verification);
mysqli_stmt_bind_result($user_verify, $user_verified);
mysqli_stmt_execute($user_verify);
mysqli_stmt_fetch($user_verify);
mysqli_stmt_close($user_verify);

// check if user exists with the email and verification code, and the verification code hasn't expired
// email should be fine as the user shouldn't have been able to change it unless intentionally modifying the form
if ( $user_verified !== 1 )
{
	mysqli_close($db);
	exit_message('Password reset error', 'Sorry, the code is incorrect or may have expired (1 hour). Please try requesting a new password reset email');
}

// verification code is valid and matches the account
// pepper + hash the user's password
$user_password = password_hash($user_raw_password . USER_PEPPER, PASSWORD_DEFAULT);

// update the user's password in the database
$user_update = mysqli_prepare($db,
	'UPDATE `users`
		SET `user_password` = ?, `user_passreset_verification` = NULL, `user_passreset_expiry` = NULL
			WHERE `user_email` = ?'
);

mysqli_stmt_bind_param($user_update, 'ss', $user_password, $user_email);
mysqli_stmt_execute($user_update);
mysqli_stmt_close($user_update);

// close DB connection
mysqli_close($db);

// email user with a notification their password has been changed
mail($user_email,
	SITE_NAME . ' - Your password has been changed',
	'Hi!' .
	'<br /><br />' .
	'Your password has been changed' .
	'<br /><br />' .
	'If you did not request a password change, please visit the site and reset your password immediately' .
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

// send a message to the user confirming their password has been changed
exit_message('Password change complete', 'Your password has been changed and you may now log in');

?>

