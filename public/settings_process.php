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

// make sure user is logged in
if ( !isset($_SESSION['user_id']) )
{
	exit_message('Not logged in', 'You must be logged in to access the account page');
}

$user_id = $_SESSION['user_id'];

// check if user is banned
require('inc/db.php');

// this will log the user out and exit if the user is banned
// if not, it will just return
checkBanned($db, $user_id);

// check user's password has been set
if ( !isset($_POST['password-old']) )
{
	exit_message('Password not set', 'Sorry, you need to enter your old password before changes can be made');
}

// check user's password matches the account
$password_get = mysqli_prepare($db, 
	'SELECT `user_password`
		FROM `users`
			WHERE `user_id` = ?'
);

mysqli_stmt_bind_param($password_get, 'i', $user_id);
mysqli_stmt_bind_result($password_get, $user_password_old);
mysqli_stmt_execute($password_get);
mysqli_stmt_fetch($password_get);
mysqli_stmt_close($password_get);

if ( !password_verify($_POST['password-old'] . USER_PEPPER, $user_password_old) )
{
	exit_message('Incorrect password', 'Sorry, your old password was incorrect');
}

// old password is correct
// continue processing user's request

// check for email change
if ( isset($_POST['email-change-submit']) )
{

	// user wants to change password
	// check for input fields
	if ( empty($_POST['email']) || empty($_POST['email-confirm']) )
	{
		exit_message('Missing fields required', 'Please ensure you fill in both the email and email confirmation field');
	}

	// check if inputs match
	if ( $_POST['email'] !== $_POST['email-confirm'] )
	{
		exit_message('Emails don\'t match', 'Please ensure both email and email confirmation are the same');
	}

	// make sure email is valid
	if ( !filter_var($_POST['email']) )
	{
		exit_message('Invalid email', 'Sorry, that email address appears to be invalid. Please go back and try again');
	}

	// email appears to be valid
	$user_email_new = $_POST['email'];

	// get existing info first
	$user_get = mysqli_prepare($db,
		'SELECT `user_email`
			FROM `users`
				WHERE `user_id` = ?'
	);

	mysqli_stmt_bind_param($user_get, 'i', $user_id);
	mysqli_stmt_bind_result($user_get, $user_email_old);
	mysqli_stmt_execute($user_get);
	mysqli_stmt_fetch($user_get);

	// compare new email to old email
	if ( $user_email_new === $user_email_old )
	{
		exit_message('Identical email', 'This email address is already being used by this account');
	}

	mysqli_stmt_close($user_get);

	// check to see if email is already being used
	$email_check = mysqli_prepare($db,
		'SELECT `user_id`
			FROM `users`
				WHERE `user_email` = ?'
	);
	
	mysqli_stmt_bind_param($email_check, 's', $user_email_new);
	mysqli_stmt_execute($email_check);
	mysqli_stmt_store_result($email_check);
	
	// check if email exists in the DB
	if ( mysqli_stmt_num_rows($email_check) !== 0 )
	{
		exit_message('Email already in use', 'Sorry, an account already exists with that email address');
	}

	mysqli_stmt_close($email_check);

	// no accounts found with that email address
	// proceed with email change

	// update DB with new email
	$user_update_email = mysqli_prepare($db,
		'UPDATE `users`
			SET `user_email` = ?, `user_email_previous` = ?, `user_verified` = 0
				WHERE `user_id` = ?'
	);

	mysqli_stmt_bind_param($user_update_email, 'ssi', $user_email_new, $user_email_old, $user_id);
	mysqli_stmt_execute($user_update_email);

	if ( mysqli_stmt_affected_rows($user_update_email) === 0 )
	{
		exit_message('Unable to change email', 'Sorry, we were unable to change your email address. Please try again');
	}

	mysqli_stmt_close($user_update_email);

	// log user out
	session_unset();
	session_destroy();

	// the email has been updated
	// take user through verification process again// generate a new code and update DB
	$user_email_verification = bin2hex(openssl_random_pseudo_bytes(16));
	
	$user_update_verification = mysqli_prepare($db,
		'UPDATE `users`
			SET `user_email_verification` = ?, `user_email_verification_sent` = NOW()
				WHERE `user_email` = ?'
	);
	
	mysqli_stmt_bind_param($user_update_verification, 'ss', $user_email_verification, $user_email_new);
	mysqli_stmt_execute($user_update_verification);
	mysqli_stmt_close($user_update_verification);
	
	// close DB connection
	mysqli_close($db);
	
	// email user with the verification link
	mail($user_email_new,
		SITE_NAME . ' - Please verify your email',
		'Hi!' .
		'<br /><br />' .
		'Thank you for registering!' .
		'<br /><br />' .
		'This site requires you to verify your email address.' .
		'<br /><br />' .
		'Please <a href="' . SITE_URL . SCRIPT_PATH . 'verify.php?email=' . $user_email_new . '&code=' . $user_email_verification . '">click here</a> to verify your email address.' .
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
	$verification_error = 'No email? Check your spam folder or <a href="resend_verification.php?email=' . $user_email_new . '&csrf-token=' . new_csrf() . '">click here to get a new email</a>';
	
	exit_message('Verification email sent', $verification_message, $verification_error);

}

elseif ( isset($_POST['password-change-submit']) )
{

	// user wants to change password
	// we already verified the old password

	// set errors array
	$errors = [];

	// check if password confirmation field has been filled in
	if ( empty($_POST['password-new-confirmation']) )
	{
		// no password confirmation set
		$errors[] = 'Password confirmation not filled in';
	}
	else
	{
		// check if password confirmaiton matches previous password
		if ( $_POST['password-new'] !== $_POST['password-new-confirmation'] )
		{
			// passwords don't match
			$errors[] = 'Password and confirmation password do not match';
		}
	}

	// compare password fields
	if ( $_POST['password-new'] !== $_POST['password-new-confirmation'] )
	{
		exit_message('Passwords don\'t match', 'The password and password confiormation fields do not match. Your password has remained unchanged');
	}

	// password fields have been filled in
	// check complexity requirements
	// check password length (set in config.php)
	if ( strlen($_POST['password-new']) < PASSWORD_MIN )
	{
		// password is too short
		$errors[] = 'Password should be at least ' . PASSWORD_MIN . ' characters long';
	}

	// password is long enough
	// see if password complexiy is required in config.php
	if  ( PASSWORD_COMPLEX )
	{
		// check if password is different to the email
		if ( $_POST['password-new'] === $_SESSION['user_email'])
		{
			$errors[] = 'Your password cannot be the same as your email';
		}

		// check password complexity
		if ( ctype_alpha($_POST['password-new']) || ctype_digit($_POST['password-new']) )
		{
			// password is letters or numbers only
			$errors[] = 'Your password should contain at least 1 letter, number and special character';
		}
		else
		{
			// password is not just letters
			if ( ctype_alnum($_POST['password-new']) )
			{
				// password is just letters and numbers
				$errors[] = 'Your password should contain at least 1 special character';
			}
			else
			{
				// password meets requirements
				$user_raw_password = $_POST['password-new'];
			}
		}
	}
	else
	{
		// password meets requirements
		$user_raw_password = $_POST['password-new'];
	}

	// if there are any errors set, let the user know
	if ( !empty($errors) )
	{
		exit_message('Password change error', 'The following errors were encountered during your password change. Please go back and try again.', $errors);
		exit;
	}

	$user_raw_password_peppered = $user_raw_password . USER_PEPPER;

	// hash the new password
	$user_password_new = password_hash($user_raw_password_peppered, PASSWORD_DEFAULT);

	// update DB
	$user_update_password = mysqli_prepare($db,
		'UPDATE `users`
			SET `user_password` = ?
				WHERE `user_id` = ?'
	);

	mysqli_stmt_bind_param($user_update_password, 'si', $user_password_new, $user_id);
	mysqli_stmt_execute($user_update_password);
	
	if ( mysqli_stmt_affected_rows($user_update_password) === 0 )
	{
		exit_message('Unable to change password', 'Sorry, we were unable to change your password. Please try again');
	}

	// password has been changed
	exit_message('Password changed', 'Your password has been changed');

}

elseif ( isset($_POST['user-delete-submit']) )
{

	// user wants to delete account

	$files_get = mysqli_prepare($db,
		'SELECT `file_id`, `file_date`
			FROM `files`
				WHERE `file_user_id` = ?
					AND `file_removed` = 0'
	);

	mysqli_stmt_bind_param($files_get, 'i', $user_id);
	mysqli_stmt_execute($files_get);
	mysqli_stmt_store_result($files_get);

	if ( mysqli_stmt_num_rows($files_get) !== 0 )
	{

		// remove files
		mysqli_stmt_bind_result($files_get, $file_id, $file_date);

		while ( mysqli_stmt_fetch($files_get) )
		{
			// remove file
			unlink(FILES_FOLDER . $file_date . '/' . $user_id . '/' . $file_id);
		}

		mysqli_stmt_close($files_get);
	
		// update DB to mark files as removed
		$files_remove = mysqli_prepare($db,
			'UPDATE `files`
				SET `file_removed` = 1
					WHERE `file_user_id` = ?
						AND `file_removed` = 0'
		);
	
		mysqli_stmt_bind_param($files_remove, 'i', $user_id);
		mysqli_stmt_execute($files_remove);
		mysqli_stmt_close($files_remove);

	}

	// close account
	$user_close = mysqli_prepare($db,
	'UPDATE `users`
		SET `user_closed` = 1
			WHERE `user_id` = ?'
	);

	mysqli_stmt_bind_param($user_close, 'i', $user_id);
	mysqli_stmt_execute($user_close);
	mysqli_stmt_close($user_close);

	// log out user
	session_unset();
	session_destroy();

	// inform user
	exit_message('Account deleted', 'Your account has been closed and all files have been removed');

}

else
{
	exit_message('No action specified', 'No action was specified so the account has remained unchanged');
}

