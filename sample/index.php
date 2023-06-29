<?php
session_start();
if (isset($_SESSION["user"])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome to PHP SDK Sample App</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome to PHP SDK Sample App</h1>
    <button class="dashboard-button" onclick="window.location.href='login.php'">Login</button>
</body>
</html>