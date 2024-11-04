<!DOCTYPE html>
<html>
<head>
    <title>Login with Descope</title>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://unpkg.com/@descope/web-component@3.25.1/dist/index.js"></script>
    <script src="https://unpkg.com/@descope/web-js-sdk@1.16.6/dist/index.umd.js"></script>
    <script type="text/javascript" src="../static/descope.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div id="container"></div>
    <script>
        function sendFormData(sessionToken, userDetails) {
            var formData = new FormData();
            formData.append("sessionToken", sessionToken);
            formData.append("userDetails", JSON.stringify(userDetails));

            var xmlHttp = new XMLHttpRequest();
            let getUrl = window.location;
            let baseUrl = getUrl.protocol + "//" + getUrl.host;

            xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                    window.location.href = `${baseUrl}/dashboard.php`;
                }
            };

            xmlHttp.open("post", `${baseUrl}/callback.php`);
            xmlHttp.send(formData);
        }

        async function getUserDetails() {
            const user = await sdk.me();
            return user;
        }

        async function handleLogout() {
            
        }

        async function handleLogin() {
            try {
                console.log("Attempting to refresh the session...");
                await sdk.refresh();

                const sessionToken = sdk.getSessionToken();
                console.log("Session token obtained:", sessionToken);

                if (!sessionToken) {
                    console.log("Session token is missing after refresh. Redirecting to login.");
                    window.location.href = 'login.php'; // Redirect to login if session token is invalid
                    return;
                }

                const user = await getUserDetails();
                console.log("User details obtained:", user);

                sendFormData(sessionToken, user.data);
            } catch (error) {
                console.log("Error during login:", error);
                sdk.logout();
                window.location.href = 'login.php'; // Redirect to login on error
            }
        }

        const refreshToken = sdk.getRefreshToken();
        const validRefreshToken = refreshToken && !sdk.isJwtExpired(refreshToken);

        if (validRefreshToken) {
            console.log("Valid refresh token found. Logging in...");
            handleLogin();
        } else {
            sdk.logout();
            console.log("No valid refresh token. Displaying login form.");
            const container = document.getElementById("container")
            container.innerHTML = '<descope-wc project-id="' + projectId + '" flow-id="sign-up-or-in"></descope-wc>';
            const wcElement = document.getElementsByTagName('descope-wc')[0];

            const onSuccess = async (e) => {
                console.log("Login successful, handling login.");
                await handleLogin(); // Wait for login and session details
            }

            const onError = (err) => console.log("Login error:", err);

            if (wcElement) {
                wcElement.addEventListener('success', onSuccess);
                wcElement.addEventListener('error', onError);
            }
        }
    </script>
</body>
</html>