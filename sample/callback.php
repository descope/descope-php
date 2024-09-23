<!DOCTYPE html>
<html>
<head>
    <title>Login Callback</title>
</head>
<body>
    <?php
    require '../vendor/autoload.php';
    use Descope\SDK\DescopeSDK;

    session_start();

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    if (!isset($_ENV['DESCOPE_PROJECT_ID'])) {
        echo "Descope Project ID not present. Please check .env file.";
        exit(1);
    }

    $descopeSDK = new DescopeSDK([
        'projectId' => $_ENV['DESCOPE_PROJECT_ID']
    ]);

    if (isset($_POST["sessionToken"]) && $descopeSDK->verify($_POST["sessionToken"])) {
        $_SESSION["user"] = json_decode($_POST["userDetails"], true);
        $_SESSION["sessionToken"] = $_POST["sessionToken"];
        
        session_write_close();

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