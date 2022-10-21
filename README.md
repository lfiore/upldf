# upldf

A file hosting script written in PHP using MySQLi for the DB.

## Script features

### User account support

* Allow / disable account creation 
* Email verification
* Forgotten password reset
* Lock user out after incorrect attempts
* Password policy enforcement
* Password / email change
* Close account feature
* Ban users (admin account)

### File management

* Allow password protected downloads
* Change / remove passwords for files (if logged in)
* Remove own files (if logged in)
* Remove files (admin account)
* File reporting

### Customisable

* Limit upload size
* Limit uploads to logged in users
* Limit download speeds for guests and logged in users

### Virus scanning

* Support for ClamAV antivirus to scan uploads

## Installation instructions

### Database

* Import upldf.sql to your database

### Files

* Copy the files and folders in the public folder to the public directory of your webserver
* Create a folder for the files outside of the public directory of your webserver, i.e. /var/upldf-files
* Rename config.sample.php to config.php and configure it with your settings

### Permissions

* Give the webserver read and write permissions for the created file storage folder

### After install

* Register a user account for yourself and set "user_admin" to 1 in the databse

## Demo

You can demo the script at https://upldf.com/

http://github.com/lfiore/upldf/
