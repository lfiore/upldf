<?php if ( !defined('IN_APP') ): exit; endif; ?>

</div>

<div class="footer">
	<ul>
		<li><?php echo SITE_NAME; ?></li>
		<li><a href="contact.php">contact</a></li>
	</ul>
</div>

<?php if ( isset($scripts) ): ?>

	<?php foreach ( $scripts as $script ): ?>

<script src="inc/js/<?php echo $script; ?>.js" type="text/javascript" defer></script>

	<?php endforeach; ?>

<?php endif; ?>

</body>
</html>

