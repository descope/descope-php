<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://unpkg.com/@descope/web-component@latest/dist/index.js"></script>
    <script src="https://unpkg.com/@descope/web-js-sdk@latest/dist/index.umd.js"></script>
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