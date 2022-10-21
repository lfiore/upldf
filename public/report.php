<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

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

// check for existing report
require('inc/db.php');

$report_get = mysqli_prepare($db,
	'SELECT `report_id`
		FROM `reports`
			WHERE `report_file_id` = ?'
);

mysqli_stmt_bind_param($report_get, 's', $file_id);
mysqli_stmt_execute($report_get);
mysqli_stmt_store_result($report_get);

if ( mysqli_stmt_num_rows($report_get) !== 0 )
{
	exit_message('Already reported', 'This file has already been reproted');
}

mysqli_stmt_close($report_get);

// get user's IP
require('inc/lib/cloudflare_ip.php');
$report_user_ip = USER_IP;

if ( !isset($_SESSION['user_id']) )
{
	$report_user_id = null;
}
else
{
	$report_user_id = $_SESSION['user_id'];
}

// add report to DB
$report_add = mysqli_prepare($db,
	'INSERT INTO `reports` (`report_file_id`, `report_user_id`, `report_user_ip`)
		VALUES (?, ?, ?)'
);

mysqli_stmt_bind_param($report_add, 'sis', $file_id, $report_user_id, $report_user_ip);
mysqli_stmt_execute($report_add);
mysqli_stmt_close($report_add);

// email admin
mail(EMAIL_REPORTS,
	SITE_NAME . ' - A file has been reported - ' . $file_id,
	'Hi!' .
	'<br /><br />' .
	'A file has been reported' .
	'<br /><br />' .
	'You can check the reported file <a href="' . SITE_URL . SCRIPT_PATH . 'view.php?id=' . $file_id . '">here</a>' .
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

// inform user
exit_message('File reported', 'The file has been reported');

