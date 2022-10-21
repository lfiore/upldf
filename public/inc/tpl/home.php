<?php if ( !defined('IN_APP') ): exit; endif; ?>

<div class="page-wrapper wide">

	<div class="page-title">welcome to <?php echo SITE_NAME; ?></div>

<?php if ( ANON_UPLOADS || isset($_SESSION['user_id']) ): ?>

	<p>Use the form below to select your file</p>
	<ul>
		<li>Your file may be up to <span class="bold-text blue-text"><?php echo MAX_FILE_SIZE; ?>MB</span> in size</li>
		<li>Virus scanning is <span class="bold-text blue-text"><?php echo ( SCAN_UPLOADS === true ? 'en' : 'dis' ); ?>abled</span></li>
	</ul>

</div>

<div id="upload-form-box" class="page-wrapper wide">

	<form id="upload-form" name="upload-form" action="upload_process.php" method="POST" enctype="multipart/form-data">

		<button id="select-file-button" name="select-file-button" type="button">Select your file</button>
		<input name="csrf-token" type="hidden" value="<?php echo new_csrf(); ?>" />
		<input id="max-file-size" name="MAX_FILE_SIZE" type="hidden" value="<?php echo MAX_FILE_SIZE_BYTES; ?>" />
		<input id="upload-file" name="file" type="file" />
		<p id="selected-file"></p>

	<?php if ( isset($_SESSION['user_id']) || ANON_PASS_PROTECT ): ?>

		<label for="upload-password">Enter a password below if you would like to password protect the download link (optional)</label>
		<input id="upload-password" class="narrow" name="password" type="password" placeholder="password..." />

	<?php endif; ?>

		<button id="upload-submit" class="button-submit" aria-label="submit upload" name="upload-submit" type="button" disabled>Upload</button>
	</form>
	
<?php else: ?>

	<p>Anonymous uploads have been disabled, please create an account or log in to upload files</p>

<?php endif; ?>

</div>