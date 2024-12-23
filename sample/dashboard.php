<?php
require '../vendor/autoload.php';
use Descope\SDK\DescopeSDK;

session_start();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

if (!isset($_SESSION["user"])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get user details and session token from session variables
$user = $_SESSION["user"];
$sessionToken = $_SESSION["sessionToken"];

$descopeSDK = new DescopeSDK([
    'projectId' => $_ENV['DESCOPE_PROJECT_ID']
]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .header {
            display: flex;
            justify-content: flex-end;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .logout-button {
            background-color: #e0e0e0;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 56px);
            padding: 0 20px;
        }
        .token-box {
            margin-top: 20px;
            background-color: #343a40;
            color: #ffffff;
            padding: 15px;
            border-radius: 5px;
            word-wrap: break-word;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <div class="header">
        <button class="btn btn-light logout-button" onclick="location.href='logout.php'">Logout</button>
    </div>
    <div class="container">
        <h1>Welcome, <?php echo ($user["name"]) ?>!</h1>
        <p>Your email: <?php echo ($user["email"]) ?></p>
        <img class="rounded-circle" src="<?php if (isset($user["picture"])) {
            echo ($user["picture"]);
                                         } ?>">
        <div class="token-box">
            <p>Your Session Token: <?php echo ($sessionToken) ?></p>
        </div>
    </div>
</body>
</html>