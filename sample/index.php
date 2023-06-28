<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<html>
<head>
  <title>Home Page</title>
</head>
<body>
  <h1>Welcome to our website!</h1>
  <a href="login.php">Login with Descope</a>
</body>
</html>