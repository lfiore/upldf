<?php if ( !defined('IN_APP') ): exit; endif; ?>

<div class="page-wrapper narrow">

	<div class="page-title">user registration</div>

	<form name="register-form" action="register_process.php" method="POST">

		<input name="csrf-token" type="hidden" value="<?php echo new_csrf(); ?>" />

		<input aria-label="email" name="email" type="email" placeholder="email..." />

		<input aria-label="email confirmation" name="email-confirmation" type="email" placeholder="email (confirm)..." />

		<input aria-label="password" name="password" type="password" placeholder="password..." />

		<input aria-label="password confirmation" name="password-confirmation" type="password" placeholder="password (confirm)..." />

		<input aria-label="submit registration" name="register-submit" type="submit" value="Register" />

	</form>

</div>