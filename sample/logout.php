<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://unpkg.com/@descope/web-component@3.25.1/dist/index.js"></script>
    <script src="https://unpkg.com/@descope/web-js-sdk@1.16.6/dist/index.umd.js"></script>
    <script type="text/javascript" src="../static/descope.js"></script>
</head>
<body>
    <script>
        logout().then((resp) => {
            // Redirect back to home page
            window.location = "/index.php";
        });

        async function logout() {
            const resp = await sdk.logout();
        }
    </script>
</body>
</html>
