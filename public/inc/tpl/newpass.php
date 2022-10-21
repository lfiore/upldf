<?php if ( !defined('IN_APP') ): exit; endif; ?>

<div class="page-wrapper narrow">

	<div class="page-title">new password</div>

	<form name="newpass-form" action="newpass_process.php" method="POST">

		<input name="verification-code" type="hidden" value="<?php echo htmlentities($_GET['code'], ENT_QUOTES); ?>" />

		<input name="csrf-token" type="hidden" value="<?php echo new_csrf(); ?>" />

		<input name="email" type="hidden" value="<?php echo htmlentities($_GET['email'], ENT_QUOTES); ?>" />

		<input aria-label="password" name="password" type="password" placeholder="password..." />

		<input aria-label="password confirmation" name="password-confirmation" type="password" placeholder="password (confirm)..." />

		<input aria-label="submit password reset" name="newpass-submit" type="submit" value="Reset password" />

	</form>

</div>

