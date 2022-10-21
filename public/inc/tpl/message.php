<?php if ( !defined('IN_APP') ): exit; endif; ?>

<div id="exit-message" class="page-wrapper wide">

	<div id="exit-message-title" class="page-title"><?php echo $title; ?></div>

	<div id="exit-messages">

<?php if ( is_array($messages) ): ?>

	<?php foreach ( $messages as $message ): ?>
		<p><?php echo $message; ?></p>
	<?php endforeach; ?>

<?php else: ?>

		<p><?php echo $messages; ?></p>

<?php endif; ?>

<?php if ( isset($errors) ): ?>

		<ul class="errors">

	<?php if ( is_array($errors) ): ?>

		<?php foreach ( $errors as $error ): ?>
			<li><?php echo $error; ?></li>
		<?php endforeach; ?>

	<?php else: ?>

		<li><?php echo $errors; ?></li>

	<?php endif; ?>

		</ul>

<?php endif; ?>	

</div>