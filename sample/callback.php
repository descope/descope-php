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
    
    echo($_POST['sessionToken']);

    if ($descopeSDK->verify($_POST['sessionToken'])) {
        $userInfo = $descopeSDK->getUser($_ENV['DESCOPE_PROJECT_ID'] . ":" . $_POST['refreshToken']);
    } else {
        header('Location: index.php');
        exit;
    }

    // Set user information into session
    session_start();
    if (isset($userInfo)) {
        $_SESSION["user"] = $userInfo;
    }

    // Redirect to dashboard
    header('Location: dashboard.php');
    ?>
</body>
</html>