<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

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
checkBanned($db, $_SESSION['user_id']);

// get list of user's files
$files_get = mysqli_prepare($db,
	'SELECT `file_id`, `file_name`, `file_size`, `file_password`, UNIX_TIMESTAMP(`file_timestamp`), `file_virus_scanned`, `file_virus_found`, `file_virus_signature`, `file_downloads`
		FROM `files`
			WHERE `file_user_id` = ?
				AND `file_removed` = 0
					ORDER BY `file_row_id`
						DESC'
);

mysqli_stmt_bind_param($files_get, 'i', $user_id);
mysqli_stmt_bind_result($files_get, $file_id, $file_name, $file_size, $file_password, $file_timestamp, $file_virus_scanned, $file_virus_found, $file_virus_signature, $file_downloads);
mysqli_stmt_execute($files_get);
mysqli_stmt_store_result($files_get);

// set a csrf token
$csrf_token = new_csrf();

$scripts[] = 'files';

require('inc/tpl/header.php');
require('inc/tpl/uploads.php');
require('inc/tpl/footer.php');

exit;