<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | U-Tracksis</title>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: rgb(20, 39, 51);
            font-family: monospace;
        }

        a {
            margin-top: .5rem;
            padding: .5rem 1rem;
            border: solid;
            color: white;
            transition: color 0.5s ease-in-out;
        }

        a:hover {
            color: red;
        }
    </style>
</head>
<body>

    <h1 style="color: wheat;">Welcome, <?= htmlspecialchars($userName) ?>!</h1>

    <h3 style="color: rgb(93, 214, 130);">You are now logged in, but...</h3>

    <h4 style="color: rgb(219, 51, 51); font-weight: 500;">This user's role does not have a configured page yet. 
        Go to <span style="color: rgb(168, 101, 245); font-weight: bold;">app/Controllers/AuthController.php</span> 
        and adjust the redirect inside the <span style="color: rgb(237, 107, 241); font-weight: bold;">login function</span>.</h4>

    <p style="color: rgb(232, 247, 98);">Your role is: >> <u><?= htmlspecialchars($role) ?></u> << </p>

    <a style="" href="<?= BASE_URL ?>/logout">Logout</a>


</body>
</html>