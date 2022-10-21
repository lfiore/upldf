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

if ( ALLOW_NEW_USERS === false )
{
	exit_message('New registrations are disabled', 'Sorry, new registrations have been disabled');
}

// check CSRF token
if ( !isset($_POST['csrf-token']) || ( check_csrf($_POST['csrf-token']) === false ) )
{
	exit_message('CSRF token mismatch', 'Sorry, there was an error with the form submission. Please go back and try again');
}

if ( !isset($_POST['register-submit']) )
{
	exit_message('Form submission error', 'The registration form was not submitted proeprly. Please go back to the registration page and try again');
}

$errors = [];

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

// check if email confirmation field has been filled in
if ( empty($_POST['email-confirmation']) )
{
	// no email confirmation set
	$errors[] = 'Email confirmation not filled in';
}
else
{
	// check if email confirmaiton matches previous email
	if ( $_POST['email'] !== $_POST['email-confirmation'] )
	{
		// emails don't match
		$errors[] = 'Email and confirmation email do not match';
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
	// password fields have been filled in
	// check complexity requirements
	// check password length (set in config.php)
	if ( strlen($_POST['password']) < PASSWORD_MIN )
	{
		// password is too short
		$errors[] = 'Password should be at least ' . PASSWORD_MIN . ' characters long';
	}

	// password is long enough
	// see if password complexiy is required in config.php
	if  ( PASSWORD_COMPLEX )
	{
		// check if password is different to the email
		if ( $_POST['password'] === $_POST['email'])
		{
			$errors[] = 'Your password cannot be the same as your email';
		}

		// check password complexity
		if ( ctype_alpha($_POST['password']) || ctype_digit($_POST['password']) )
		{
			// password is letters or numbers only
			$errors[] = 'Your password should contain at least 1 letter, number and special character';
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

// if there are any errors set, let the user know
if ( !empty($errors) )
{
	exit_message('Registration error', 'The following errors were encountered during registration. Please go back and try again.', $errors);
	exit;
}

// no errors, continue with registration
// check the database to see if a user exists with that email

// open DB connection
require('inc/db.php');

$email_check = mysqli_prepare($db,
	'SELECT `user_id`
		FROM `users`
			WHERE `user_email` = ?'
);

mysqli_stmt_bind_param($email_check, 's', $user_email);
mysqli_stmt_execute($email_check);
mysqli_stmt_store_result($email_check);

// check if email exists in the DB
if ( mysqli_stmt_num_rows($email_check) !== 0 )
{
	exit_message('Registration error', 'Sorry, an account already exists with that email address');
}

mysqli_stmt_close($email_check);

// no user exists with that email
// pepper + hash the user's password
$user_password = password_hash($user_raw_password . USER_PEPPER, PASSWORD_DEFAULT);

// generated a verification string if required
if ( VERIFY_EMAIL )
{
	$user_email_verification = bin2hex(openssl_random_pseudo_bytes(16));
}
else
{
	$user_email_verification = NULL;
}

// get user's IP
require('inc/lib/cloudflare_ip.php');
$user_reg_ip = USER_IP;

// insert user into the database
$user_register = mysqli_prepare($db,
	'INSERT INTO `users` (`user_email`, `user_email_verification`, `user_password`, `user_reg_ip`)
		VALUES (?, ?, ?, ?)'
);

mysqli_stmt_bind_param($user_register, 'ssss', $user_email, $user_email_verification, $user_password, $user_reg_ip);
mysqli_stmt_execute($user_register);
mysqli_stmt_close($user_register);

// close DB connection
mysqli_close($db);

// send a message to the user confirming their registration is complete

$registration_message[] = 'Thank you for registering. ';

if ( VERIFY_EMAIL )
{

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

	$registration_message[0] .= 'Please click on the link in your email to verify your email address';
	$verification_error = 'No email? Check your spam folder or <a href="resend_verification.php?email=' . $user_email . '&csrf-token=' . new_csrf() . '">click here to get a new email</a>';

	exit_message('Registration complete', $registration_message, $verification_error);

}
else
{
	$registration_message[0] .= 'You may now log in with your new account';

	exit_message('Registration complete', $registration_message);
}

?>

