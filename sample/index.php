<!DOCTYPE html>
<html>
<head>
    <title>Login with Descope</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.auth0.com/js/auth0/9.13/auth0.min.js"></script>
    <script>
        function createToken(userDetails, sessionToken, redirectURL, projectId) 
        {
            var formData = new FormData();
            formData.append("userId", userDetails.userId);
            formData.append("userName", userDetails.name);
            formData.append("sessionToken", sessionToken);
            formData.append("projectId", projectId);

            var xmlHttp = new XMLHttpRequest();
            let getUrl = window.location;
            let baseUrl = getUrl.protocol + "//" + getUrl.host;

            xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                    window.location = `${baseUrl}/${redirectURL}`;
                }
            };
            xmlHttp.open("post", `${baseUrl}/src/descope-token.php`);
            xmlHttp.send(formData);
        }

        const onSuccess = (e) => {
            const sessionToken = e.detail.sessionJwt;

            createToken(
                e?.detail?.user,
                sessionToken,
                e.target.getAttribute("redirect_url"),
                e.target.getAttribute("project-id")
            );
        };

        const onError = (err) => console.log(err);

        async function inject_flow(projectId, flowId, redirectUrl) {
            const sdk = Descope({
                projectId: projectId,
                persistTokens: true,
                autoRefresh: true,
            });

            const refreshToken = sdk.getRefreshToken();
            const validRefreshToken = refreshToken && !sdk.isJwtExpired(refreshToken);
            if (validRefreshToken) {
                sdk.refresh();
                const sessionToken = sdk.getSessionToken();
                const user = await sdk.me();
                createToken(user.data, sessionToken, redirectUrl, projectId);
            } else {
                const e = document.getElementById("descope_flow_div");
                e.innerHTML = `<descope-wc project-id=${projectId} flow-id=${flowId} redirect_url=${redirectUrl}></descope-wc>`;
                const wcElement = document.getElementsByTagName("descope-wc")[0];
                if (wcElement) {
                    wcElement.addEventListener("success", onSuccess);
                    wcElement.addEventListener("error", onError);
                }
            }
        }

        async function logout(projectId, loginPageUrl) {
            const sdk = Descope({
                projectId: projectId,
                persistTokens: true,
                autoRefresh: true,
            });
            
            const resp = await sdk.logout();
            
            // Redirect back to login page
            window.location = loginPageUrl;
        }
    </script>
</head>
<body class="text-center">
    <div class="container">
        <div class="py-5 text-center">
            <h2>Login with Descope</h2>
            <div class="descope_flow_div"></div>
        </div>
    </div>
</body>
</html>