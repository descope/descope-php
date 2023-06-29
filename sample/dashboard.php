<?php
session_start();
print_r($_SESSION["user"]);
if (!isset($_SESSION["user"])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Assume user data was saved in $_SESSION['user'] after successful login
$user = unserialize($_SESSION["user"]);
print_r($user);
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
        </div>
    </div>
</body>
</html>