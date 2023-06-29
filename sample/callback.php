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

    $sessionToken = $_POST["sessionToken"];

    if (isset($sessionToken) && $descopeSDK->verify($sessionToken)) {
        // $userInfo = $descopeSDK->getUser($_ENV['DESCOPE_PROJECT_ID'] . ":" . $_POST['refreshToken']);

        // Set user name into session variable
        session_start();
        $_SESSION["user"] = $_POST["userDetails"];
        $_SESSION["sessionToken"] = $sessionToken;
    }
    
    // Redirect to dashboard
    header('Location: dashboard.php');
    ?>
</body>
</html>