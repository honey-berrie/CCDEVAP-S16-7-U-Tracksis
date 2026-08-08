<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>U-Tracksis - Under Maintenance</title>
  <style>
    :root {
      --primary: #0c1a32;
      --navy-accent: #182e59;
      --bg-main: #fbfaf6;
      --text: #1A1A1A;
      --text-light: #64748b;
      --border: #e2e8f0;
      --warning: #f59e0b;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--bg-main);
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      color: var(--text);
      padding: 24px;
    }
    .card {
      max-width: 460px;
      width: 100%;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 40px 32px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(12, 26, 50, 0.08);
    }
    .icon {
      width: 56px;
      height: 56px;
      margin: 0 auto 20px;
      border-radius: 50%;
      background: rgba(245, 158, 11, 0.12);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
    }
    h1 {
      font-size: 20px;
      margin: 0 0 12px;
      color: var(--primary);
    }
    p {
      margin: 0 0 8px;
      color: var(--text-light);
      line-height: 1.5;
      font-size: 14.5px;
    }
    .retry {
      margin-top: 24px;
      display: inline-block;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      background: var(--navy-accent);
      color: #fff;
      text-decoration: none;
      font-family: inherit;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
    }
    .retry:hover { opacity: 0.9; }
    .retry:disabled { opacity: 0.7; cursor: default; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon">🛠️</div>
    <h1>U-Tracksis is under maintenance</h1>
    <p>We're making some updates to the system. Please check back in a little while.</p>
    <p>If you're an administrator, please log in from the admin portal to continue.</p>
    <button class="retry" type="button" id="retryBtn">Try again</button>
  </div>

  <script>
    document.getElementById('retryBtn').addEventListener('click', async function () {
      const btn = document.getElementById('retryBtn');
      const originalText = btn.textContent;
      btn.textContent = 'Checking...';
      btn.disabled = true;

      try {
        const res = await fetch('<?= BASE_URL ?>/maintenance-status', {
          method: 'GET',
          credentials: 'include',
        });
        const data = await res.json();

        if (!data.maintenance) {
          window.location.href = '<?= BASE_URL ?>/login';
          return;
        }

        btn.textContent = 'Still under maintenance';
        setTimeout(function () {
          btn.textContent = originalText;
          btn.disabled = false;
        }, 2000);
      } catch (err) {
        btn.textContent = originalText;
        btn.disabled = false;
      }
    });
  </script>
</body>
</html>