<?php
require_once __DIR__ . '/../config/app.php';

unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_role']);

setFlash('info', 'You have been successfully signed out.');
header('Location: /login.php');
exit;
