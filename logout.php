<?php
require_once 'config/db.php';
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
