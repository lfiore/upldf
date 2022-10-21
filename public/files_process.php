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

// check file ID is valid
if ( !ctype_alnum($_POST['file-id']) )
{
	exit_message('Invalid ID', 'Sorry, the file ID is invalid. Please check the link and try again');
}

$file_id = $_POST['file-id'];

// check for file deletion
if ( isset($_POST['file-remove-submit']) )
{

	// user wants to remove the file
	// get file details from the server
	$file_details = mysqli_prepare($db,
	'SELECT `file_date`, `file_removed`
		FROM `files`
			WHERE `file_id` = ?
				AND `file_user_id` = ?'
	);

	mysqli_stmt_bind_param($file_details, 'si', $file_id, $user_id);
	mysqli_stmt_execute($file_details);
	mysqli_stmt_store_result($file_details);

	if ( mysqli_stmt_num_rows($file_details) === 0 )
	{
		exit_message('Unable to find file', 'This file doesn\'t exist or it may belong to another user');
	}

	mysqli_stmt_bind_result($file_details, $file_date, $file_removed);
	mysqli_stmt_fetch($file_details);
	mysqli_stmt_close($file_details);

	if ( $file_removed === 1 )
	{
		exit_message('File removed', 'This file has already been removed');
	}

	// file exists in the DB and hasn't already been removed
	// continue with removal

	// set removed to 1 where the file ID and user ID match
	$file_remove = mysqli_prepare($db,
		'UPDATE `files`
			SET `file_removed` = 1
				WHERE `file_id` = ?
					AND `file_user_id` = ?'
	);

	mysqli_stmt_bind_param($file_remove, 'si', $file_id, $user_id);
	mysqli_stmt_execute($file_remove);
	mysqli_stmt_close($file_remove);

	// remove the file from the server
	if ( !unlink(FILES_FOLDER . $file_date . '/' . $user_id . '/' . $file_id) )
	{
		if ( !file_exists($file_folder . $file_id) )
		{
			exit_message('Could not remove file', 'Sorry, this file was not found');
		}
		else
		{
			exit_message('Could not remove file', 'Sorry, this file could not be removed');
		}
	}

}

// check for password change
if ( isset($_POST['password-change-submit']) )
{

	// user wants to remove the file
	// get file details from the server
	$file_details = mysqli_prepare($db,
	'SELECT `file_password`, `file_removed`
		FROM `files`
			WHERE `file_id` = ?
				AND `file_user_id` = ?'
	);

	mysqli_stmt_bind_param($file_details, 'si', $file_id, $user_id);
	mysqli_stmt_execute($file_details);
	mysqli_stmt_store_result($file_details);

	if ( mysqli_stmt_num_rows($file_details) === 0 )
	{
		exit_message('Unable to find file', 'This file doesn\'t exist or it may belong to another user');
	}

	mysqli_stmt_bind_result($file_details, $file_password, $file_removed);
	mysqli_stmt_fetch($file_details);
	mysqli_stmt_close($file_details);

	if ( $file_removed === 1 )
	{
		exit_message('File removed', 'This file has already been removed');
	}

	// file exists in the DB and hasn't already been removed
	// continue with password change

	// pepper password
	$file_password_new_raw_peppered = $_POST['file-password'] . FILE_PEPPER;

	// does the user want to remove or change the password

	// remove password
	if ( empty($_POST['file-password']) )
	{

		// is there a password set?
		if ( $file_password === null )
		{
			exit_message('No password set', 'This file already has no password set');
		}

		else
		{

			// remove password
			$password_remove = mysqli_prepare($db,
			'UPDATE `files`
				SET `file_password` = NULL
					WHERE `file_id` = ?
						AND `file_user_id` = ?'
			);


			mysqli_stmt_bind_param($password_remove, 'si', $file_id, $user_id);
			mysqli_stmt_execute($password_remove);
			mysqli_stmt_close($password_remove);

			exit_message('Password removed', 'The password has been removed for this file');

		}

	}

	else
	{

		// user wants to update/set a password
		// check to see if passwords match
		if ( password_verify($file_password_new_raw_peppered, $file_password) )
		{
			exit_message('Identical password', 'The new password is the same as the old password');
		}

		else
		{

			// update password
			$file_password_new = password_hash($file_password_new_raw_peppered, PASSWORD_DEFAULT);

			$password_update = mysqli_prepare($db,
			'UPDATE `files`
				SET `file_password` = ?
					WHERE `file_id` = ?
						AND `file_user_id` = ?'
			);

			mysqli_stmt_bind_param($password_update, 'ssi', $file_password_new, $file_id, $user_id);
			mysqli_stmt_execute($password_update);
			mysqli_stmt_close($password_update);

			exit_message('Password updated', 'The password has been updated for this file');

		}

	}

}

exit_message('No action specified', 'No action was specified so the file has remained unchanged');

