<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | U-Tracksis</title>
</head>
<body>

    <h1>Welcome, <?= htmlspecialchars($userName) ?></h1>

    <a href="<?= BASE_URL ?>/logout">Logout</a>

    <p>You are now logged in.</p>

    <p>Your role is: <?= htmlspecialchars($role) ?></p>

</body>
</html>