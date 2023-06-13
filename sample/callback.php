<!DOCTYPE html>
<html>
<head>
    <title>Login Callback</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require 'vendor/autoload.php';
    use Descope\SDK\DescopeSDK;

    $descopeSDK = new DescopeSDK([
        'projectId' => $_ENV['DESCOPE_PROJECT_ID']
    ]);
    
    if ($descopeSDK->verify()) {
        // $userInfo = $descopeSDK->getUser();
        echo "Valid session token!";
    } else {
        echo "Session token does not have valid";
        header('Location: index.php');
        exit;
    }

    // Set user information into session
    session_start();
    $_SESSION['user'] = "username here";

    // Redirect to dashboard
    header('Location: dashboard.php');
    ?>
</body>
</html>