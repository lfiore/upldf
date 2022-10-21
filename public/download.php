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

// check file id
if ( !ctype_alnum($_POST['download-id']) )
{
	exit_message('Invalid ID', 'Sorry, the file ID is invalid. Please check the link and try again');
}

$file_id = $_POST['download-id'];

// check to see if user has requested this file via view.php
if ( !isset($_SESSION[$file_id]) )
{
	exit_message('Invalid request', 'Please ensure you are accessing this file via the download page');
}

// check user has waited the required time and not force-enabled the download button
if ( !isset($_SESSION['user_id']) )
{
	$time = $_SESSION[$file_id]['time'] + DOWNLOAD_TIME_ANON;
}

else
{
	$time = $_SESSION[$file_id]['time'] + DOWNLOAD_TIME_USER;
}

if ( time() < $time )
{
	exit_message('Download error', 'Sorry, unable to process download. Please go back and try again');
}

// removed file ID from user session
unset($_SESSION[$file_id]);
session_write_close();

// retrieve file details
require('inc/db.php');

// prepare query
$file_get = mysqli_prepare($db,
	'SELECT `file_name`, `file_date`, `file_size`, `file_password`, `file_user_id`
		FROM `files`
			WHERE `file_id` = ?'
);

mysqli_stmt_bind_param($file_get, 's', $file_id);
mysqli_stmt_bind_result($file_get, $file_name, $file_date, $file_size, $file_password, $file_user_id);
mysqli_stmt_execute($file_get);
mysqli_stmt_fetch($file_get);
mysqli_stmt_close($file_get);

// check password
if ( $file_password !== NULL )
{

	if (  !isset($_POST['download-password']) || !password_verify($_POST['download-password'] . FILE_PEPPER, $file_password) )
	{
		exit_message('Incorrect password', 'Sorry, the password you entered is incorrect');
	}

}

// set user ID to 0 for anonymous uploads
if ( $file_user_id === null )
{
	$file_user_id = 0;
}

// build file location
$file_location = FILES_FOLDER . $file_date . '/' . $file_user_id . '/' . $file_id;

// make sure file exists
if ( !file_exists($file_location) )
{
	exit_message('Download error', 'Sorry, this file could not be found');
}

// update downloads counter
$download_update = mysqli_prepare($db,
	'UPDATE `files`
		SET `file_downloads` = `file_downloads` + 1
			WHERE `file_id` = ?'
);

mysqli_stmt_bind_param($download_update, 's', $file_id);
mysqli_stmt_execute($download_update);
mysqli_stmt_close($download_update);
mysqli_close($db);

// prepare file for download and send to client
header('Content-Description: File Transfer');
header('Content-Type: application/force-download');
header('Content-Length: ' . $file_size);

// sanitise filename
$file_name = mb_ereg_replace('([^\w\s\d\-_~,;\[\]\(\).])', '_', $file_name);
$file_name = mb_ereg_replace('([\.]{2,})', '', $file_name);
$file_name = trim($file_name);

header('Content-Disposition: attachment; filename="' . $file_name . '"');

// check if user is logged in and any speed limits are set
if ( !isset($_SESSION['user_id']) )
{
	$download_speed = DOWNLOAD_SPEED_ANON * 1024;
}

else
{
	$download_speed = DOWNLOAD_SPEED_USER * 1024;
}

// remove script execution time limit
set_time_limit(0);

if ( $download_speed !== 0 )
{

	// open file
	$file = fopen($file_location, 'rb');

	// read file
	while ( !feof($file) )
	{
		// output to browser
		echo fread($file, round($download_speed));

		// flush content
		flush();

		// wait a second before sending the rest
		sleep(1);

	}

	// close file
	fclose($file);

}
else
{
	readfile($file_location);
}

