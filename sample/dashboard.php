<?php
session_start();
if (!isset($_SESSION["user"])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get user details and session token from session variables
$user = $_SESSION["user"];
$sessionToken = $_SESSION["sessionToken"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="header">
        <button class="btn btn-light logout-button" onclick="location.href='logout.php'">Logout</button>
    </div>
    <div class="container">
        <div class="py-5 text-center">
            <h1>Welcome, <?php echo ($user["name"]) ?>!</h1>
            <p>Your email: <?php echo ($user["email"]) ?></p>
            <img class="rounded-circle" src="<?php echo ($user["picture"]) ?>">
            <p>Your session token: <?php echo ($sessionToken) ?></p>
        </div>
    </div>
</body>
</html>