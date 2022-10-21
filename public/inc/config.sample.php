<?php

// check if file was accessed directly and exit if so
if ( !defined('IN_APP') )
{
	exit;
}

/* SITE SETTINGS */

// the name of the website
define('SITE_NAME', 'my upldf site');

// the URL for the website (excluding the folder and http/https prefix)
define('CONF_SITE_HOSTNAME', 'example.com');

// the prefix for the website (http:// or https:// if SSL is enabled)
// if neither is specified, it will default to https://
define('CONF_PREFIX', 'https://');

// the folder for the script (if applicable)
// i.e. if the site is installed at example.com/upldf the script path would be 'upldf'
define('CONF_SCRIPT_PATH', '');

// contact email which will be displayed publicly on the website
define('EMAIL_CONTACT', 'support@example.com');

// email which will be used to send emails (i.e. email verification codes)
// usually something like noreply@domain.com
define('EMAIL_FROM', 'noreply@example.com');

// reported file notifications will be sent to this email address
define('EMAIL_REPORTS', 'reports@example.com');

// if you are using cloudflare to proxy traffic, set this to true
// otherwise, all logged IPs will belong to Cloudflare and not the user
// don't set this to true if not using Cloudflare or it will cause problems
define('CLOUDFLARE', false);

/* DATABASE SETTINGS */

// database details
define('DB_SERVER', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'db username');
define('DB_PASS', 'db password');
define('DB_NAME', 'db name');

/* REGISTRATION SETTINGS */

// require users to verify their email address
define('VERIFY_EMAIL', true);

// minimum length for user passwords (10+ recommended)
define('PASSWORD_MIN', 10);

// enforce special character requirements? (recommended)
define('PASSWORD_COMPLEX', true);

// for additional security, you can use a global pepper on top of individual salts for passwords
// it added to a user's password before it's salted and hashed
// this is a string of extra characters and can be anything you want (the longer the better)
// leave it blank if you don't want to use it
// WARNING: you must not lose or change this, or previously stored password hashes will no longer be valid
define('USER_PEPPER', 'a long string of random characters');

// allow new users
define('ALLOW_NEW_USERS', true);

/* LOGIN SETTINGS */

// how many incorrect password attempts before the user is locked out
define('LOCKOUT_MAX_ATTEMPTS', 5);

// lockout time in seconds after failed login attempts
define('LOCKOUT_TIME', 600);

/* BAN SETTINGS */

// should a user's files be removed if they are banned?
define('BAN_REMOVE_FILES', true);

/* UPLOAD SETTINGS */

// place this folder outside of the publicly accessible webserver folder
// make sure the webserver (usually www-data has access to read and write to this folder)
define('CONF_FILES_FOLDER', '/path/to/files/folder/from/root');

// maximum file size per file in megabytes (M) for anon users
define('MAX_FILE_SIZE_ANON', '5');

// maximum file size per file in megabytes (M) for logged in users
define('MAX_FILE_SIZE_USER', '20');

// can anonymous users upload files without an account?
define('ANON_UPLOADS', true);

// should password protected download links be enabled (including logged in users)
define('PASS_PROTECT', true);

// can anonymous users (not logged in) password protect download links?
define('ANON_PASS_PROTECT', true);

// for additional security, you can use a global pepper on top of individual salts for passwords
// it added to a file's password before it's salted and hashed
// this is a string of extra characters and can be anything you want (the longer the better)
// leave it blank if you don't want to use it
// WARNING: you must not lose or change this, or previously stored password hashes will no longer be valid
define('FILE_PEPPER', 'another long string of random characters!');

/* VIRUS SCAN SETTINGS */

// scan uploads for viruses using clamav
// this uses clamav-daemon which is running constantly with all signaures stored in memory, to enable quick scans
// it can use a lot of memory, even causing issues on servers with 2GB RAM
define('SCAN_UPLOADS', false);

// clamd socket file
// this may be different depending on the Linux distribution you're using
define('CLAMD_SOCKET', '/var/run/clamav/clamd.ctl');

// should viruses be allowed (with a warning on the download page)
// set to false to reject the upload if a virus is found
define('ALLOW_VIRUSES', true);

// if the virus scan fails for whatever reason, should the upload be allowed (with a notice on the download page)
// set to false to reject the upload if the file can't be scanned
// setting this to false might be annoying to the user if their file can't be uploaded because of an issue connecting to ClamAV
define('ALLOW_SCAN_FAIL', true);

/* DOWNLOAD SETTINGS */

// define the amount of time an anonymous user must wait before the file download starts
// set to 0 to disable
define('DOWNLOAD_TIME_ANON', 10);

// define the amount of time a logged in user must wait before the file download starts
// set to 0 to disable
define('DOWNLOAD_TIME_USER', 0);

// define the maximum download speed for an anonymous user in KB/s
// set to 0 for unlimited
define('DOWNLOAD_SPEED_ANON', 200);

// define the maximum download speed for a logged in user in KB/s
define('DOWNLOAD_SPEED_USER', 0);

