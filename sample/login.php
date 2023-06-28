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
            const sessionToken = e.detail.sessionJwt;
            const projectId = e.target.getAttribute("project-id");
            const refreshToken = e.detail.refreshJwt;

            var formData = new FormData();
            formData.append("sessionToken", sessionToken);
            formData.append("projectId", projectId);
            formData.append("refreshToken", refreshToken);

            var xmlHttp = new XMLHttpRequest();
            let getUrl = window.location;
            let baseUrl = getUrl.protocol + "//" + getUrl.host;

            xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                    window.location = `${baseUrl}/callback.php`;
                }
            };
            
            xmlHttp.open(
                "post",
                `${baseUrl}/callback.php`
            );
            xmlHttp.send(formData);
        }

        const onError = (err) => console.log(err);

        wcElement.addEventListener('success', onSuccess)
        wcElement.addEventListener('error', onError)
    </script>
</head>
</html>
