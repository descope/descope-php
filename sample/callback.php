<!DOCTYPE html>
<html>
<head>
    <title>Login Callback</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require '../vendor/autoload.php';
    use Descope\SDK\DescopeSDK;

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    $descopeSDK = new DescopeSDK([
        'projectId' => $_ENV['DESCOPE_PROJECT_ID']
    ]);
    
    if (isset($_POST["sessionToken"]) && $descopeSDK->verify($_POST["sessionToken"])) {
        session_start();

        $_SESSION["user"] = json_decode($_POST["userDetails"], true);
        $_SESSION["sessionToken"] = $_POST["sessionToken"];

        // Redirect to dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        // Redirect to login page
        header('Location: login.php');
        exit();
    }
    ?>
</body>
</html>