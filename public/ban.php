<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

if ( !isset($_SESSION['user_admin']) || !isset($_SESSION['user_id']) )
{
	exit_message('Unauthorised', 'You must be logged in as an admin user to access this page');
}

$admin_id = $_SESSION['user_id'];

// check if admin is banned
require('inc/db.php');

// this will log the user out and exit if the user is banned
// if not, it will just return
checkBanned($db, $admin_id);

// check CSRF token
if ( !isset($_GET['csrf-token']) || ( check_csrf($_GET['csrf-token']) === false ) )
{
	exit_message('CSRF token mismatch', 'Sorry, there was an error with the form submission. Please go back and try again');
}

if ( !ctype_alnum($_GET['id']) )
{
	exit_message('Invalid ID', 'Sorry, that user ID is invalid');
}

$user_id = $_GET['id'];

// ban account
$user_ban = mysqli_prepare($db,
'UPDATE `users`
	SET `user_banned` = 1
		WHERE `user_id` = ?'
);

mysqli_stmt_bind_param($user_ban, 'i', $user_id);
mysqli_stmt_execute($user_ban);
mysqli_stmt_close($user_ban);

if ( BAN_REMOVE_FILES === true )
{

	// get file info from DB
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
			$date_folder = FILES_FOLDER . $file_date . '/';

			if ( $user_id === null )
			{
				$file_folder = $date_folder . '0/';
			}
			else
			{
				$file_folder = $date_folder . $user_id . '/';
			}

			unlink($file_folder . $file_id);

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

	// all files have been removed

}

// inform user
$ban_message = 'The user has been banned';

if ( BAN_REMOVE_FILES )
{
	$ban_message .= ' and all files have been removed';
}

exit_message('User banned', $ban_message);

