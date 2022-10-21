<?php if ( !defined('IN_APP') ): exit; endif; ?>

	<div class="page-wrapper wide">

		<div class="page-title">Hi <?php echo htmlentities($_SESSION['user_email'], ENT_QUOTES); ?></div>

		<p>Welcome to your uploads page. Here, you can view your uploaded files, remove them or add/change/remove passwords for them</p>

		<p>To do this, click on the <span class="blue-text bold-text">show / hide file actions</span> text to the right of the file you want to modify</p>

	</div>

	

<?php if ( mysqli_stmt_num_rows($files_get) === 0 ): ?>

		<div class="file page-wrapper wide">

			<div class="bold-text file-title">No files found</div>

			<p>You haven't uploaded any files yet</p>

		</div>

<?php else: ?>

	<?php while ( mysqli_stmt_fetch($files_get) ): ?>

		<div class="file page-wrapper wide">

			<div class="bold-text file-title">
				<a href="view.php?id=<?php echo $file_id; ?>">
					<?php echo htmlentities($file_name, ENT_QUOTES); ?>
				</a>
			</div>

			<div class="row">

				<div class="column key">size</div>

				<div class="column value">
					<span class="file-info bold-text">
						<?php echo sizeHR($file_size); ?>
					</span>
				</div>

			</div>

			<div class="row">

				<div class="column key">upload date</div>

				<div class="column value">
					<span class="file-info bold-text">
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
					<span class="file-info bold-text">
						<?php echo $file_downloads; ?>
					</span>
				</div>

			</div>

			<div class="row">

				<div class="column key">password</div>

				<div class="column value">
					<span class="file-info bold-text">
						<?php echo ( isset($file_password) ? 'yes' : 'no' ); ?>
					</span>
				</div>

			</div>

			<div class="file-actions">

				<div id="file-actions-<?php echo $file_id; ?>" class="file-actions-toggle bold-text" bold-text file-title">
					show / hide file actions
				</div>

				<form id="file-actions-<?php echo $file_id; ?>-form" class="file-actions-form" name="file-actions-form-<?php echo $file_id; ?>" action="files_process.php" method="POST">

					<input name="csrf-token" type="hidden" value="<?php echo $csrf_token; ?>" />
					<input name="file-id" type="hidden" value="<?php echo $file_id; ?>" />

					<p>Use the button below to remove the file</p>

					<input name="file-remove-submit" class="red-button" type="submit" value="remove file" />

					<p>the form below to change or remove the password</p>

					<input class="narrow" name="file-password" type="password" placeholder="Leave blank to remove password..." />
					<input name="password-change-submit" type="submit" value="change / remove password" />

				</form>

			</div>

		</div>

	<?php endwhile; ?>

<?php endif; ?>