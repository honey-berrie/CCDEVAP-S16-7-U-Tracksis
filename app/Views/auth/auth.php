<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | U-Tracksis</title>
</head>
<body>

    <h1>U-Tracksis</h1>
    <p>Sign in to your account</p>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/login" method="POST">
        <div>
            <label>Email</label><br>
            <input type="email" name="email" required autofocus>
        </div>
        <br>
        <div>
            <label>Password</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        <button type="submit">Sign In</button>
    </form>

    <p><small>All demo passwords: demo1234</small></p>

</body>
</html>