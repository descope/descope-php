<!DOCTYPE html>
<html>
<head>
    <title>Login with Descope</title>

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f2f2f2;
        }
        #container {
            width: 100%;
            max-width: 400px;
        }
    </style>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://unpkg.com/@descope/web-component@latest/dist/index.js"></script>
    <script src="https://unpkg.com/@descope/web-js-sdk@latest/dist/index.umd.js"></script>
    <script type="text/javascript" src="../static/descope.js"></script>
</head>
    <div id="container"></div>
    <script>
        sdk.refresh()
        const container = document.getElementById("container")
        
        container.innerHTML = '<descope-wc project-id="' + projectId + '" flow-id="sign-up-or-in"></descope-wc>';
        const wcElement = document.getElementsByTagName('descope-wc')[0]

        const onSuccess = (e) => {
            sdk.refresh()
            
            const user = getUserDetails().then((user) => {
                var formData = new FormData();
                const sessionToken = sdk.getSessionToken();

                formData.append("sessionToken", sessionToken);
                formData.append("projectId", e.target.getAttribute("project-id"));
                formData.append("userDetails", JSON.stringify(user.data));

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
            })

            async function getUserDetails() {
                const user = await sdk.me();
                return user;
            }
        }

        const onError = (err) => console.log(err);

        if (wcElement) {
            wcElement.addEventListener('success', onSuccess)
            wcElement.addEventListener('error', onError)
        }
    </script>
</head>
</html>
