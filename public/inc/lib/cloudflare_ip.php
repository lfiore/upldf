<?php

// check if file was accessed directly and exit if so
if ( !defined('IN_APP') )
{
	exit;
}

// check if server is behind Cloudflare
if ( CLOUDFLARE === true )
{

	require('ip_in_range.php');

	// cloudflare's IP ranges
	$cf_ipv4 = explode("\n", file_get_contents('https://www.cloudflare.com/ips-v4'));
	$cf_ipv6 = explode("\n", file_get_contents('https://www.cloudflare.com/ips-v6'));

	// get user's real IP if the server is using Cloudflare as a proxy

	// assume IP is invalid
	$valid_ip = false;

	// check if IP is IPv4
	if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
	{
		foreach ($cf_ipv4 as $range)
		{
			if (ipv4_in_range($_SERVER['REMOTE_ADDR'], $range))
			{
				$valid_ip = true;
				define('USER_IP', $_SERVER['HTTP_CF_CONNECTING_IP']);
				break;
			}
		}
	}

	// check if IP is IPv6
	elseif (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
	{
		foreach ($cf_ipv6 as $range)
		{
			if (ipv6_in_range($_SERVER['REMOTE_ADDR'], $range))
			{
				$valid_ip = true;
				define('USER_IP', $_SERVER['HTTP_CF_CONNECTING_IP']);
				break;
			}
		}
	}

	// IP is invalid
	else
	{
		exit_message('Invalid IP Address', 'It appears your IP address is invalid. Please contact the server administrator.');
	}

	if ($valid_ip === false)
	{
		exit_message('IP Spoofing Detected', 'It appears you are not connecting via the Cloudflare network. Please contact the server administrator.');
	}

}
else
{
	define('USER_IP', $_SERVER['REMOTE_ADDR']);
}