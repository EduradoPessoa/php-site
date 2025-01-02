<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = Auth::getInstance();
$auth->logout();

$_SESSION['flash_message'] = 'Você foi desconectado com sucesso!';
$_SESSION['flash_type'] = 'success';

header('Location: login.php');
exit;