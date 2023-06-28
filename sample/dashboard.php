<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Assume user data was saved in $_SESSION['user'] after successful login
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="py-5 text-center">
            <h1>Welcome, <?php echo $user['nickname'] ?>!</h1>
            <p>Your email: <?php echo $user['email'] ?></p>
            <img class="rounded-circle" src="<?php echo $user['picture'] ?>">
        </div>
    </div>
</body>
</html>