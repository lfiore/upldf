<?php if ( !defined('IN_APP') ): exit; endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="upldf file hosting">
	<title><?php echo SITE_NAME; ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
	<link href="inc/css/global.css" type="text/css" rel="stylesheet" />
</head>

<body>

<div class="header">

	<div class="header-wrapper wide">

		<div class="navbar">
			<ul>
				<li><a href="index.php">home</a></li>

	<?php if ( isset($_SESSION['user_id']) ): ?>

				<li><a href="uploads.php">uploads</a></li>
				<li><a href="settings.php">settings</a></li>
				<li><a href="logout.php">logout</a></li>

	<?php else: ?>

				<li><a href="login.php">login</a></li>
				<li><a href="register.php">register</a></li>

	<?php endif; ?>

			</ul>
		</div>

		<div class="site_name">
			<a href="<?php echo SITE_URL . SCRIPT_PATH; ?>"><?php echo SITE_NAME; ?></a>
		</div>

	</div>

</div>

<div class="content">

