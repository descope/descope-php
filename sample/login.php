<!DOCTYPE html>
<html>
<head>
    <title>Login with Descope</title>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://unpkg.com/@descope/web-component@latest/dist/index.js"></script>
    <script src="https://unpkg.com/@descope/web-js-sdk@latest/dist/index.umd.js"></script>
    <script type="text/javascript" src="../static/descope.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
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
                    window.location = `${baseUrl}/dashboard.php`;
                }
            };
                
            xmlHttp.open("post", `${baseUrl}/callback.php`);
            xmlHttp.send(formData);
        }

        async function getUserDetails() {
            const user = await sdk.me();
            return user;
        }
        
        const refreshToken = sdk.getRefreshToken();
        const validRefreshToken = refreshToken && !sdk.isJwtExpired(refreshToken);

        if (validRefreshToken) {
            console.log("Valid refresh token found. Logging in...");
            sdk.refresh();
            const user = getUserDetails().then((user) => {
                const sessionToken = sdk.getSessionToken();
                sendFormData(sessionToken, user.data);
            });
        } else {
            const container = document.getElementById("container")
            container.innerHTML = '<descope-wc project-id="' + projectId + '" flow-id="sign-up-or-in"></descope-wc>';
            const wcElement = document.getElementsByTagName('descope-wc')[0]

            const onSuccess = (e) => {
                sdk.refresh();
                
                const user = getUserDetails().then((user) => {
                    const sessionToken = sdk.getSessionToken();
                    sendFormData(sessionToken, user.data);
                });
            }

            const onError = (err) => console.log(err);

            if (wcElement) {
                wcElement.addEventListener('success', onSuccess)
                wcElement.addEventListener('error', onError)
            }
        }
    </script>
</head>
</html>
