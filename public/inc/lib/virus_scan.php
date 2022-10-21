<?php

// check if file was accessed directly and exit if so
if ( !defined('IN_APP') )
{
	exit;
}

// returns 0 for a clean file
// returns 1 for a virus found
// returns 2 for an error connecting to the socket

function virus_scan($file)
{

	// connect to clamd socket
	$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
	socket_connect($socket, CLAMD_SOCKET);

	// send a scan command with the file
	$scan = 'SCAN ' . $file;
	socket_send($socket, $scan, strlen($scan), 0);
	socket_recv($socket, $output, 100, 0);

	$socket_error = socket_last_error($socket);
	socket_close($socket);

	// check for any errors
	if ( $socket_error === 0 )
	{

		// get scan result
		$raw_output_array = explode(':', $output);
		$raw_output = end($raw_output_array);
		$clean_output = rtrim($raw_output);
		$scan_result_array = explode(' ', $clean_output);
		$scan_result = end($scan_result_array);

		// check scan result
		if ( $scan_result === 'OK' )
		{
			// no errors and no virus found
			return array(0, null);
		}

		elseif ( $scan_result === 'FOUND' )
		{
			// virus found or file not found
			$virus_signature = current(explode(' ', ltrim($clean_output)));
			return array(1, $virus_signature);
		}

		else
		{
			// another issue - i.e. file not found
			return array(2, null);
		}

	}

	else
	{
		// scan failed
		return array(2, null);
	}
}

