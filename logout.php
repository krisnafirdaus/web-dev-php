<?php
// logout.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->logout();

setFlashMessage('You have been logged out successfully', 'success');
header('Location: login.php');
exit();