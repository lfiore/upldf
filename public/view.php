<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

// check file id
if ( !ctype_alnum($_GET['id']) )
{
	exit_message('Invalid ID', 'Sorry, the file ID is invalid. Please check the link and try again');
}

$file_id = $_GET['id'];

// check if user is banned if logged in
if ( isset($_SESSION['user_id']) )
{

	require('inc/db.php');

	// this will log the user out and exit if the user is banned
	// if not, it will just return
	checkBanned($db, $_SESSION['user_id']);

}

// retrieve file details
require_once('inc/db.php');

// prepare query
$file_get = mysqli_prepare($db,
	'SELECT `file_name`, `file_date`, `file_size`, `file_password`, UNIX_TIMESTAMP(`file_timestamp`), `file_user_id`, `file_virus_scanned`, `file_virus_found`, `file_virus_signature`, `file_downloads`, `file_removed`
		FROM `files`
			WHERE `file_id` = ?'
);

mysqli_stmt_bind_param($file_get, 's', $file_id);
mysqli_stmt_bind_result($file_get, $file_name, $file_date, $file_size, $file_password, $file_timestamp, $file_user_id, $file_virus_scanned, $file_virus_found, $file_virus_signature, $file_downloads, $file_removed);
mysqli_stmt_execute($file_get);
mysqli_stmt_store_result($file_get);

if ( mysqli_stmt_num_rows($file_get) === 0 )
{
	exit_message('File not found', 'No file exists with this ID');
}

mysqli_stmt_fetch($file_get);

if ( $file_removed === 1 )
{
	exit_message('File removed', 'Sorry, this file has been removed');
}

mysqli_stmt_close($file_get);
mysqli_close($db);

// set download 
$_SESSION[$file_id]['time'] = time();

// get wait time
if ( !isset($_SESSION['user_id']) )
{

	// get wait time for anon user
	if ( DOWNLOAD_TIME_ANON !== 0 )
	{
		$wait_time = DOWNLOAD_TIME_ANON;
	}

}

else 
{

	// get wait time for logged in user
	if ( DOWNLOAD_TIME_USER !== 0 )
	{
		$wait_time = DOWNLOAD_TIME_USER;
	}

}

$csrf_token = new_csrf();

$scripts[] = 'view';

require('inc/tpl/header.php');
require('inc/tpl/view.php');
require('inc/tpl/footer.php');

exit;