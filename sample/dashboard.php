<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="py-5 text-center">
            <?php
            session_start();

            if (!isset($_SESSION['user'])) {
                echo '<div class="alert alert-danger" role="alert">You are not logged in.</div>';
                exit;
            }

            $user = $_SESSION['user'];
            ?>
            <!-- <h1>Welcome, <?php echo $user['nickname'] ?>!</h1>
            <p>Your email: <?php echo $user['email'] ?></p>
            <img class="rounded-circle" src="<?php echo $user['picture'] ?>"> -->
        </div>
    </div>
</body>
</html>