<?php
session_start();
if (isset($_SESSION["DS_SESSION"])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{% block title %} {% endblock %}</title>
    <script src="https://unpkg.com/@descope/web-component@latest/dist/index.js"></script>
    <script src="https://unpkg.com/@descope/web-js-sdk@latest/dist/index.umd.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400;500&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Golos Text', sans-serif;
            color: rgb(34, 34, 34);
            width: 100%;
            height: 100%;
        }
        .layout {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .row {
            flex-direction: row;
        }
        .link {
            margin: 3vh;
            color: rgb(0, 142, 95);
            padding: 14px 20px;
            background-color: rgb(234, 234, 234);
            border-radius: 10px;
            text-decoration: none;
        }
        .logout {
            border: none;
            cursor: pointer;
            color: rgb(234, 234, 234);
            background-color: rgb(0, 142, 95);
            font-size: 1em;
        }
        button:focus {
            outline: none;
        }
        h1, h2 {
            margin: 0;
        }
        h2 {
            margin-top: 4vh;
            margin-bottom: 2vh;
        }
        .title {
            color: rgb(4, 161, 132);
            margin-top: 10vh;
            margin-bottom: 3vh;
        }
        .email {
            background-color: rgb(193, 255, 234);
            padding: 7px 20px;
            border-radius: 100px;
        }
        .home {
            color: rgb(25, 144, 122);
        }
        .secret {
            background-color: rgb(241, 241, 241);
            border-radius: 12px;
            padding: 4vh;
        }
    </style>
</head>
<body>
    <div class="layout">
        <h1 class="title">PHP SDK Sample App</h1>

        <div class="layout row">
            <a class="link" href="/dashboard.php">Dashboard</a>
        </div>
        <script src="https://unpkg.com/@descope/web-component@latest/dist/index.js"></script>
        <script src="https://unpkg.com/@descope/web-js-sdk@latest/dist/index.umd.js"></script>
        <script type="text/javascript" src="../static/descope.js"></script>
    </div>
</body>
</html>
