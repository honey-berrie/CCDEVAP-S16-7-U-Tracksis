<?php

$display = "login";

if (isset($error_type, $error)){
    $display = $error_type;
}

if (isset($redirect, $success)){
    $display = $redirect;
}

?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Authentication | U-Tracksis</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/bootstrap.min.css" />

    <!-- Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth/auth.css" />
  </head>
  <body>
    <div class="auth-card">
      <!-- LOGIN SECTION -->
      <section class="auth-section" id="login-section" style="display: <?= $display === 'register' ? 'none' : 'block' ?>;">
        <h1>Login</h1>
        <p class="auth-subtitle">Enter your email and password to sign in.</p>

        <form id="loginForm" action="<?= BASE_URL ?>/login" method="post">
          <div class="mb-3">
            <label for="login-email" class="form-label">Email</label>
            <input
              type="email"
              class="form-control"
              id="login-email"
              name="email"
              placeholder="Enter your email"
              required
            />
          </div>

          <div class="mb-3">
            <label for="login-password" class="form-label">Password</label>
            <input
              type="password"
              class="form-control"
              id="login-password"
              name="password"
              placeholder="Enter your password"
              required
            />
          </div>
          
          <?php if (isset($error_type, $error) && $error_type === 'login'): ?>
            <div class="mb-3 text-center">
                <p class="error-msg" style="color: red; font-weight: 500;"><?= htmlspecialchars($error) ?></p>
            </div>
          <?php endif; ?>

          <?php if (isset($redirect, $success) && $redirect === 'login'): ?>
            <div class="mb-3 text-center">
                <p class="error-msg" style="color: green; font-weight: 500;"><?= htmlspecialchars($success) ?></p>
            </div>
          <?php endif; ?>

          <button type="submit" class="btn-omnitrace">
            Sign in
          </button>

          <p class="footer-text">
            Don't have an account? <a href="#register-section">Register</a>
          </p>
        </form>
      </section>

      <!-- REGISTER SECTION -->
      <section class="auth-section" id="register-section" style="display: <?= $display === 'login' ? 'none' : 'block' ?>;">
        <h1>Create your account</h1>
        <p class="auth-subtitle">
          Access all that our site has to offer with a single account. All
          fields are required.
        </p>

        <form id="registerForm" action="<?= BASE_URL ?>/register" method="post">
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label for="firstname" class="form-label">First Name</label>
              <input
                type="text"
                class="form-control"
                id="firstname"
                name="firstname"
                placeholder="Enter your first name"
                required
              />
            </div>
            <div class="col-6">
              <label for="lastname" class="form-label">Last Name</label>
              <input
                type="text"
                class="form-control"
                id="lastname"
                name="lastname"
                placeholder="Enter your last name"
                required
              />
            </div>
          </div>

          <div class="mb-3">
            <label for="register-email" class="form-label">Email</label>
            <input
              type="email"
              class="form-control"
              id="register-email"
              name="email"
              placeholder="Enter your email"
              required
            />
          </div>

          <div class="mb-3">
            <label for="register-password" class="form-label">Password</label>
            <input
              type="password"
              class="form-control"
              id="register-password"
              name="password"
              placeholder="Create a password"
              required
              minlength="8"
            />
          </div>

          <div class="mb-3">
            <label for="confirm" class="form-label">Confirm Password</label>
            <input
              type="password"
              class="form-control"
              id="confirm"
              name="confirm"
              placeholder="Confirm your password"
              required
            />
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Account Type</label>
            <div class="role-group">
              <button type="button" class="btn-role active" data-role="student">
                Student
              </button>
              <button type="button" class="btn-role" data-role="faculty">
                Faculty
              </button>
            </div>
            <input
              type="hidden"
              id="accountType"
              name="accountType"
              value="student"
            />
          </div>

          <?php if (isset($error_type, $error) && $error_type === 'register'): ?>
            <div class="mb-3 text-center">
                <p class="error-msg"  style="color: red; font-weight: 500;"><?= htmlspecialchars($error) ?></p>
            </div>
          <?php endif; ?>

          <button type="submit" class="btn-omnitrace">
            Create Account
          </button>

          <p class="footer-text">
            Already have an account? <a href="#login-section">Sign in</a>
          </p>
        </form>
      </section>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>

    <script src="<?= BASE_URL ?>/js/auth/auth.js"></script>

  </body>
</html>
