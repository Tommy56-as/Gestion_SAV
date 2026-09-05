<?php
require_once __DIR__ . '/inc/bootstrap.php';
unset($_SESSION['super_admin_id'], $_SESSION['super_admin_nom'], $_SESSION['super_admin_email']);
header('Location: super_login.php');
exit;
