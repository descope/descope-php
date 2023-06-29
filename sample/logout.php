<?php
session_start();
session_destroy();

// Redirect back to home page
header('Location: /index.php');