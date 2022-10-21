<?php if ( !defined('IN_APP') ): exit; endif; ?>

<div class="page-wrapper narrow">

<div class="page-title">password reset</div>

<form name="passreset-form" action="passreset_process.php" method="POST">

	<input name="csrf-token" type="hidden" value="<?php echo new_csrf(); ?>" />

	<input aria-label="email" name="email" type="email" placeholder="email..." />

	<input aria-label="submit password reset" name="passreset-submit" type="submit" value="Reset password" />

</form>

</div>