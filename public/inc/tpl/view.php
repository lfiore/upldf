<?php if ( !defined('IN_APP') ): exit; endif; ?>

	<div class="page-wrapper wide">

		<div class="page-title"><?php echo htmlentities($file_name, ENT_QUOTES); ?></div>

		<div class="row">
			<div class="column key">size</div>
			<div class="column value">
				<span class="file-info bold-text blue-text">
					<?php echo sizeHR($file_size); ?>
				</span>
			</div>
		</div>
		<div class="row">
			<div class="column key">upload date</div>
			<div class="column value">
				<span class="file-info bold-text blue-text">
					<?php echo date('jS F Y', $file_timestamp); ?>
				</span>
			</div>
		</div>

<?php if ( $file_virus_scanned === 1 ): ?>

		<div class="row">
			<div class="column key">virus scan</div>
			<div class="column value">
				<span class="file-info bold-text <?php echo ( $file_virus_found === 0 ? 'virus-clean' : 'virus-detected' ) ?>">
					<?php echo ( $file_virus_found === 0 ? 'no virus detected' : 'virus detected (' . htmlentities($file_virus_signature, ENT_QUOTES) . ')' ) ?>
				</span>
			</div>
		</div>

<?php endif; ?>

		<div class="row">
			<div class="column key">downloads</div>
			<div class="column value">
				<span id="file-downloads" class="file-info bold-text blue-text"><?php echo $file_downloads; ?></span>
			</div>
		</div>

	</div>

	<div id="download-form-box" class="page-wrapper wide">

		<form id="download-form" name="download-form" action="download.php" method="POST">

			<input name="csrf-token" type="hidden" value="<?php echo $csrf_token; ?>" />
			<input name="download-id" type="hidden" value="<?php echo $file_id; ?>" />

<?php if ( !is_null($file_password) ): ?>

			<label for="download-password">This download is password protected. Please enter the password below</label>
			<input id="download-password" class="narrow" name="download-password" type="password" placeholder="password..." />

<?php endif; ?>

<?php if ( isset($wait_time) ): ?>

			<p id="wait-message">Please wait <span id="wait-time" class="blue-text"><?php echo $wait_time; ?></span> seconds before you can download this file</p>

<?php endif; ?>

			<button id="download-submit" aria-label="submit download" name="download-submit" type="button" disabled>Download file</button>

		</form>
	
	</div>

<?php if ( !isset($_SESSION['user_admin']) ): ?>
	
	<div id="admin-report-file" class="view-file-action wide">
		<a class="bold-text red-text" href="report.php?id=<?php echo $file_id; ?>&csrf-token=<?php echo $csrf_token; ?>">Report this file</a>
	</div>

<?php else: ?>

	<div id="admin-tools">

		<div id="admin-file-remove" class="view-file-action wide">
			<a class="bold-text red-text" href="remove.php?id=<?php echo $file_id; ?>&csrf-token=<?php echo $csrf_token; ?>">Remove this file</a>
		</div>

	<?php if ( $file_user_id !== null ): ?>

		<div id="admin-user-ban" class="view-file-action wide">
			<a class="bold-text red-text" href="ban.php?id=<?php echo $file_user_id; ?>&csrf-token=<?php echo $csrf_token; ?>">Ban this user and remove all files</a>
		</div>

	<?php endif; ?>

	</div>

<?php endif; ?>