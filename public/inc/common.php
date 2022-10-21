<?php

// check if file was accessed directly and exit if so
if ( !defined('IN_APP') )
{
	exit;
}

session_start();

// clean up paths
define('SITE_HOSTNAME', rtrim(CONF_SITE_HOSTNAME, '/'));

if ( ( CONF_PREFIX !== 'http://' ) || ( CONF_PREFIX !== 'https://' ) )
{
	define('PREFIX', 'https://');
}

else
{
	define('PREFIX', CONF_PREFIX);
}

define('CONF_SITE_URL', PREFIX . SITE_HOSTNAME);

define('SITE_URL', rtrim(CONF_SITE_URL, '/') . '/');

if ( CONF_SCRIPT_PATH !== '' )
{
	define('SCRIPT_PATH', rtrim(CONF_SCRIPT_PATH, '/') . '/');
}
else
{
	define('SCRIPT_PATH', '');
}

define('FILES_FOLDER', rtrim(CONF_FILES_FOLDER, '/') . '/');

// set file size depending on whether user is logged in
if ( !isset($_SESSION['user_id']) )
{
	define('MAX_FILE_SIZE', MAX_FILE_SIZE_ANON);
}

else
{
	define('MAX_FILE_SIZE', MAX_FILE_SIZE_USER);
}

define('MAX_FILE_SIZE_BYTES', ( MAX_FILE_SIZE * 1048576 ) );

// script to display messages/errors to the user and exit
// $script will include js files from the js directory
function exit_message($title, $messages, $errors = null, $script = null)
{

	$scripts[] = $script;

	require('inc/tpl/header.php');
	require('inc/tpl/message.php');
	require('inc/tpl/footer.php');
	exit;

}

// CSRF protection
// generate csrf token
function new_csrf()
{

	$token = bin2hex(openssl_random_pseudo_bytes(32));
	$_SESSION['csrf'] = $token;
	return $token;

}

// compare csrf token against the token stored in the user's session
function check_csrf($token)
{

	if ( isset($_SESSION['csrf']) && ( $_SESSION['csrf'] === $token ) )
	{
		unset($_SESSION['csrf']);
		return true;
	}

	return false;

}

// generate human readable sizes from bytes
function sizeHR($size)
{

	if ( $size > 1048576 )
	{
		return round( ( $size / 1048576 ), 2 ) . 'MB';
	}

	else
	{
		return round( ( $size / 1024 ), 2 ) . 'KB';
	}

}

function checkBanned($db, $user_id) {

	$ban_get = mysqli_prepare($db,
		'SELECT `user_banned`
			FROM `users`
				WHERE `user_id` = ?'
	);

	mysqli_stmt_bind_param($ban_get, 'i', $user_id);
	mysqli_stmt_bind_result($ban_get, $user_banned);
	mysqli_stmt_execute($ban_get);
	mysqli_stmt_fetch($ban_get);
	mysqli_stmt_close($ban_get);

	if ( $user_banned === 1 )
	{
		session_unset();
		session_destroy();
	
		exit_message('Banned', 'This account has been banned and you have been logged out');
	}
	else
	{
		return;
	}

}