<?php

// define IN_APP constant for included files
define('IN_APP', true);

require('inc/config.php');
require('inc/common.php');

$scripts[] = 'home';

require('inc/tpl/header.php');
require('inc/tpl/home.php');
require('inc/tpl/footer.php');

exit;

