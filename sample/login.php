<!DOCTYPE html>
<html>
<head>
    <title>Login with Descope</title>
    <div id="container"></div>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://unpkg.com/@descope/web-component@latest/dist/index.js"></script>
    <script src="https://unpkg.com/@descope/web-js-sdk@latest/dist/index.umd.js"></script>
    <script type="text/javascript" src="../static/descope.js"></script>
    <script>
        sdk.refresh()
        const container = document.getElementById("container")
        
        container.innerHTML = '<descope-wc project-id="' + projectId + '" flow-id="sign-up-or-in"></descope-wc>';
        const wcElement = document.getElementsByTagName('descope-wc')[0]

        const onSuccess = (e) => {
            sdk.refresh()
            
            const sessionToken = sdk.getSessionToken();
            const user = getUserDetails().then((user) => {
                var formData = new FormData();
                formData.append("sessionToken", sessionToken);
                formData.append("projectId", e.target.getAttribute("project-id"));
                formData.append("userDetails", JSON.stringify(user.data));

                var xmlHttp = new XMLHttpRequest();
                let getUrl = window.location;
                let baseUrl = getUrl.protocol + "//" + getUrl.host;
                
                xmlHttp.open("post", `${baseUrl}/callback.php`);
                xmlHttp.send(formData);

                // window.location = `${baseUrl}/dashboard.php`;
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
