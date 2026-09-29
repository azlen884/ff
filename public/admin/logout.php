<?php
require_once __DIR__ . '/../../config/app.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);

setFlash('info', 'Admin session terminated safely.');
header('Location: /admin/login.php');
exit;
