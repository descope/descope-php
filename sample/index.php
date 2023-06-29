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

    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f2f2f2;
        }
        h1 {
            margin-bottom: 30px;
        }
        .dashboard-button {
            padding: 10px 20px;
            font-size: 1.2em;
            background-color: #008CBA;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .dashboard-button:hover {
            background-color: #007B9E;
        }
    </style>
</head>
<body>
    <h1>Welcome to PHP SDK Sample App</h1>
    <button class="dashboard-button" onclick="window.location.href='login.php'">Login</button>
</body>
</html>