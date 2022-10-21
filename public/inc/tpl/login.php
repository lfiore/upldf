<?php if ( !defined('IN_APP') ): exit; endif; ?>

<div class="page-wrapper narrow">

	<div class="page-title">user login</div>

	<form name="login-form" action="login_process.php" method="POST">

		<input name="csrf-token" type="hidden" value="<?php echo new_csrf(); ?>" />

		<input aria-label="email" name="email" type="email" placeholder="email..." />

		<input aria-label="password" name="password" type="password" placeholder="password..." />

		<input aria-label="submit login" name="login-submit" type="submit" value="Log in" />

		<p>Forgot your password? <a href="passreset.php">click here</a></p>

	</form>

</div>