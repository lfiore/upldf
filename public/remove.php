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
	exit_message('Invalid ID', 'Sorry, that file ID is invalid');
}

$file_id = $_GET['id'];

// get file info from DB
$file_get = mysqli_prepare($db,
	'SELECT `file_date`, `file_user_id`, `file_removed`
		FROM `files`
			WHERE `file_id` = ?'
);

mysqli_stmt_bind_param($file_get, 's', $file_id);
mysqli_stmt_execute($file_get);
mysqli_stmt_store_result($file_get);

if ( mysqli_stmt_num_rows($file_get) === 0 )
{
	exit_message('No file found', 'No file was found in the database with this ID');
}

mysqli_stmt_bind_result($file_get, $file_date, $file_user_id, $file_removed);
mysqli_stmt_fetch($file_get);
mysqli_stmt_close($file_get);

if ( $file_removed === 1 )
{
	exit_message('File already removed', 'This file has already been removed');
}

// remove file
$date_folder = FILES_FOLDER . $file_date . '/';

if ( $file_user_id === null )
{
	$file_folder = $date_folder . '0/';
}
else
{
	$file_folder = $date_folder . $file_user_id . '/';
}

unlink($file_folder . $file_id);

// update DB to mark files as removed
$file_remove = mysqli_prepare($db,
	'UPDATE `files`
		SET `file_removed` = 1
			WHERE `file_id` = ?'
);

mysqli_stmt_bind_param($file_remove, 's', $file_id);
mysqli_stmt_execute($file_remove);
mysqli_stmt_close($file_remove);

// inform user
exit_message('File removed', 'The file has been removed');

