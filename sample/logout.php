<?php
session_start();
session_destroy();

unset($_COOKIE['DS_SESSION']);

setcookie('DS_SESSION', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);

// Redirect back to home page
header('Location: /index.php');