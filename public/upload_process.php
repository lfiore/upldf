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

// check if user is banned if logged in
if ( isset($_SESSION['user_id']) )
{

	require('inc/db.php');

	// this will log the user out and exit if the user is banned
	// if not, it will just return
	checkBanned($db, $_SESSION['user_id']);

}

// check for anonymous uploads
if ( ANON_UPLOADS === false && !isset($_SESSION['user_id']) )
{
	exit_message('Anonymous uploads are disabled', 'Please log in to upload files');
}

// make sure a file was submitted
if ( !isset($_FILES['file']) )
{
	exit_message('Invalid form submission', 'Please make sure you selected a file and try again');
}

// check for upload errors
if ( $_FILES['file']['error'] !== 0 )
{
	print_r($_FILES);
	exit_message('Upload error', 'Sorry, an error was encountered during upload. Please try again');
}

// check file size
if ( $_FILES['file']['size'] > MAX_FILE_SIZE_BYTES )
{
	exit_message('File too large', 'Sorry, this file is too big. The maximum file size is ' . MAX_FILE_SIZE . 'MB');
}

$file_size = $_FILES['file']['size'];

// check for a password
if ( !empty($_POST['password']) )
{

	// check if password protection is allowed
	if ( PASS_PROTECT === false )
	{
		exit_message('Password protection is disabled', 'Sorry, password protection is disabled. Please try again without a password');
	}

	// password protection is allowed
	// check for anon password protection
	if ( ( ANON_PASS_PROTECT === false ) && !isset($_SESSION['user_id']) )
	{
		exit_message('Password protection is disabled', 'Sorry, password protection is disabled for anonymous users. Please try again without a password, or log in');
	}

	// pepper + hash the file's password
	$file_password = password_hash($_POST['password'] . FILE_PEPPER, PASSWORD_DEFAULT);

}

// no password set
else
{
	$file_password = null;
}

// scan the file for viruses if enabled
if ( SCAN_UPLOADS )
{

	require('inc/lib/virus_scan.php');

	// modify permissions to give access to ClamAV
	chmod($_FILES['file']['tmp_name'], 0604);

	// scan for viruses
	// returns (0, null) for a clean file
	// returns (1, virus)_string for a virus found
	// returns (2, null) for any errors scanning the file
	$virus_scan = virus_scan($_FILES['file']['tmp_name']);

	// restore permissions
	chmod($_FILES['file']['tmp_name'], 0600);

	$virus_result = $virus_scan[0];
	$file_virus_signature = $virus_scan[1];

	if ( $virus_result === 0 )
	{
		// scan completed and no virus was detected
		$file_virus_scanned = 1;
		$file_virus_found = 0;
	}

	elseif ( $virus_result === 1 )
	{

		// scan completed and virus was detected
		if ( ALLOW_VIRUSES === false )
		{
			exit_message('Virus detected', 'Sorry, the file you uploaded contains a virus and is not allowed');
		}

		$file_virus_scanned = 1;
		$file_virus_found = 1;

	}

	elseif ( $virus_result === 2 )
	{

		if ( ALLOW_SCAN_FAIL === false )
		{
			// an error was deteced during the scan
			exit_message('Virus scan error', 'Sorry, there was an issue while scanning this file so the file cannot be uploaded');
		}

		$file_virus_scanned = 0;
		$file_virus_found = 0;

	}

	else
	{
		// unspecified error during the virus scan, exit the script
		exit_message('Unidentified virus scan error', 'Sorry, there was an unidentified error while scanning this file so the file cannot be uploaded');
	}

}

else
{
	// file not scanned for viruses
	$file_virus_scanned = 0;
	$file_virus_found = 0;
}

// all checks completed
// file exists, size, logged in (optional), scanned (optional), password (optional)

// create a new folder in the files folder based on the date if it doesn't already exist
$file_date = date('dmy');
$date_folder = FILES_FOLDER . $file_date . '/';

if ( !file_exists($date_folder) && !is_dir($date_folder) )
{
	mkdir($date_folder, 0700);
	mkdir($date_folder . '0', 0700);
	touch($date_folder . '0/' . 'index.html');
}

// create a user for the folder if they're logged in
if ( isset($_SESSION['user_id']) )
{

	$file_folder = $date_folder . $_SESSION['user_id'] . '/';

	if ( !file_exists($file_folder) && !is_dir($file_folder) )
	{
		mkdir($file_folder, 0700);
		touch($file_folder . 'index.html');
	}

}

// use 0 folder
else
{
	$file_folder = $date_folder . '0/';
}

// generate an ID for the file
require_once('inc/db.php');

// prepare query
$file_id_exists = mysqli_prepare($db, 'SELECT EXISTS(SELECT 1 FROM `files` WHERE `file_id` = ? LIMIT 1)');

// create ID and check if it exists in the DB
do
{
	// create ID
	$file_id = '';
	$chars = 'ACDEFHJKLMNPQRTUVWXYZabcdefghijkmnopqrstuvwxyz23479';
	for ( $i = 0; $i < 5; ++$i )
	{
		$file_id .= $chars[mt_rand(0, 50)];
	}

	mysqli_stmt_bind_param($file_id_exists, 's', $id);
	mysqli_stmt_execute($file_id_exists);
	mysqli_stmt_bind_result($file_id_exists, $result);
	mysqli_stmt_fetch($file_id_exists);
	mysqli_stmt_close($file_id_exists);
}
while ($result === 1);

// move the file to the folder
if ( !move_uploaded_file($_FILES['file']['tmp_name'], $file_folder . $file_id) )
{
	exit_message('File upload error', 'Sorry, there was an issue moving the file. Please try again');
}

// amend permissions of file
chmod($file_folder . $file_id, 0600);

// insert file details into DB
// prepare query
$file_insert = mysqli_prepare($db,
	'INSERT INTO `files` (`file_id`, `file_name`, `file_date`, `file_size`, `file_password`, `file_user_id`, `file_user_ip`, `file_virus_scanned`, `file_virus_found`, `file_virus_signature`)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

echo  mysqli_error($db);

// assign values
$file_name = $_FILES['file']['name'];
$file_size = $_FILES['file']['size'];
$file_user_id = $_SESSION['user_id'];

// get user's IP
require('inc/lib/cloudflare_ip.php');
$file_user_ip = USER_IP;

mysqli_stmt_bind_param($file_insert, 'sssisisiis', $file_id, $file_name, $file_date, $file_size, $file_password, $file_user_id, $file_user_ip, $file_virus_scanned, $file_virus_found, $file_virus_signature);
mysqli_stmt_execute($file_insert);
mysqli_stmt_close($file_insert);

mysqli_close($db);

// redirect user to the download page
header('location: ' . SITE_URL . SCRIPT_PATH . 'view.php?id=' . $file_id);
exit;

