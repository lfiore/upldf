<?php if ( !defined('IN_APP') ): exit; endif; ?>

<div class="page-wrapper wide">

	<div class="page-title">change email</div>

	<form name="email-change-form" action="settings_process.php" method="POST">

		<input name="csrf-token" type="hidden" value="<?php echo $csrf_token; ?>" />

		<p>Enter your old password, along with your new email twice to confirm it</p>

		<p class="bold-text red-text">Please note, upon changing your email address you will be required to validate your new email before you can access your account</p>

		<input class="narrow" aria-label="old password" name="password-old" type="password" placeholder="(old) password..." />

		<input class="narrow" aria-label="new email" name="email" type="email" placeholder="email..." />

		<input class="narrow" aria-label="new email confirmation" name="email-confirm" type="email" placeholder="email (confirm)..." />

		<input aria-label="submit email change" name="email-change-submit" type="submit" value="Change email" />

	</form>

</div>

<div class="page-wrapper wide">

	<div class="page-title">change password</div>

	<form name="password-change-form" action="settings_process.php" method="POST">

		<input name="csrf-token" type="hidden" value="<?php echo $csrf_token; ?>" />

		<p>Enter your old password, along with your new password twice to confirm it</p>

		<input class="narrow" aria-label="old password" name="password-old" type="password" placeholder="(old) password..." />

		<input class="narrow" aria-label="new password" name="password-new" type="password" placeholder="(new) password..." />

		<input class="narrow" aria-label="new password confirmation" name="password-new-confirmation" type="password" placeholder="(new) password (confirm)..." />

		<input aria-label="submit password change" name="password-change-submit" type="submit" value="Change password" />

	</form>

</div>

<div class="page-wrapper wide">

	<div class="page-title">close account</div>

	<form name="user-delete-form" action="settings_process.php" method="POST">

		<input name="csrf-token" type="hidden" value="<?php echo $csrf_token; ?>" />

		<div id="user-delete-container">

			<div id="show-user-delete-presubmit">

				<label for="user-delete-presubmit">Enter your old password, then click the button to close your account and remove all files</label>

				<input id="user-delete-password" class="narrow" aria-label="old password" name="password-old" type="password" placeholder="(old) password..." />

				<button id="user-delete-presubmit" name="user-delete-presubmit" type="button">Close my account</button>

			</div>

			<div id="show-user-delete-submit" class="bold-text red-text">

				<label for="user-delete-submit">Warning: Clicking the button below will permanently close your account and all files</label>

				<input id="user-delete-submit" class="red-button" aria-label="submit user delete" name="user-delete-submit" type="submit" value="CLOSE MY ACCOUNT PERMANENTLY" />

			</div>
		
		</div>

	</form>

</div>

