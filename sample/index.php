<?php
require '../vendor/autoload.php';
use Descope\SDK\DescopeSDK;

session_start();
if (isset($_SESSION["user"])) {
    // header('Location: dashboard.php');
    // exit();
}

$descopeSDK = new DescopeSDK([
    'projectId' => 'P2OkfVnJi5Ht7mpCqHjx17nV5epH',
    'managementKey' => 'K2gqWEix95sbiQGlkwQPBa1PZNgngMhbzygh09qZ0Ssfwd9pmX0SNkskO8OTs0MuE3MY26C'
]);

$response = $descopeSDK->password->signUp("kevin@descope.com", "Peak6518@!", ["loginId" => "kevin@descope.com", "name" => "Kevin Gao"], ["customClaims" => new stdClass()]);
print($jwt_response);
    
if ($descopeSDK->verify('eyJhbGciOiJSUzI1NiIsImtpZCI6IlAyT2tmVm5KaTVIdDdtcENxSGp4MTduVjVlcEgiLCJ0eXAiOiJKV1QifQ.eyJhbXIiOlsib2F1dGgiXSwiZHJuIjoiRFMiLCJleHAiOjE3MTY0NDM5ODIsImlhdCI6MTcxNjQ0MzM4MiwiaXNzIjoiUDJPa2ZWbkppNUh0N21wQ3FIangxN25WNWVwSCIsInJleHAiOiIyMDI0LTA2LTIwVDA1OjQ5OjQyWiIsInN1YiI6IlUyZ2JSaG02MEMyMktia2IxNFFTamlNUVh1ZnkiLCJ0ZW5hbnRzIjp7IlQyU3J3ZUw1SjJ5OFlPaDhEeURiR3BaWGVqQkEiOnt9fX0.I3OkPNrkoX0AtP12SV1i3l0nct-dElR6euRc_-jnt_Gg7lSw_p62DjQxOdNAklsloqwWiNw1jyVqu_CEoJ7rTenXIq0zokA2aUvCJfmvyo0dQnO7hn85x03qShSmnSkUt40tWxCX142-qt62I0CPHTJSppTjOp3qkQHjEeuqKCwTY0r5fxlmFE_cjI8e-W0AuCtkb5u5fwal3etRvBA-hfSsY-OFCVL5YLPWhIR4zuQEqpBRokiZY-ymyk3LnBcokBj0_W0KTpZYt0DC5frG2G26y1VcwF0kav3Sd_EMVbVJZRw6Z34yrwrQBpBcQcFumA4Xiphy0fXCfgtY47XZTQ')) {

    $_SESSION["user"] = json_decode($_POST["userDetails"], true);
    $_SESSION["sessionToken"] = $_POST["sessionToken"];

    // Redirect to dashboard
    // header('Location: dashboard.php');
    // exit();
} else {
    // Redirect to login page
    // header('Location: login.php');
    // exit();
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Welcome to PHP SDK Sample App</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome to PHP SDK Sample App</h1>
    <button class="dashboard-button" onclick="window.location.href='login.php'">Login</button>
</body>
</html>