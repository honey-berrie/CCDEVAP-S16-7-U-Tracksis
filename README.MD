# U-Tracksis — Thesis Management & Consultation System

## 🛠 Email Configuration (XAMPP)

### php.ini — Mail Settings

Configure `C:\xampp\php\php.ini`:

```ini
[mail function]
SMTP=smtp.gmail.com
smtp_port=587
sendmail_from = kyrellerramos@gmail.com
sendmail_path = "C:\xampp\sendmail\sendmail.exe -t"
```

> ⚠️ **Important:** The Gmail address here must match the sender email configured in `/api/mailer.php`.

---

## 📧 PHPMailer Setup (Primary Method)

PHPMailer is installed standalone at `api/lib/PHPMailer/` (cloned via Git — no Composer required):


### Configuration — `api/mailer.php`

```php
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'kyrellerramos@gmail.com'; // your email here
$mail->Password   = 'slzgaumqzhrcufsz'; // Gmail App Password (no spaces)
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;

$mail->setFrom('kyrellerramos@gmail.com', 'U-Tracksis'); // your email here & app name
```

### How to Get a Gmail App Password

1. Enable [2-Step Verification](https://myaccount.google.com/security) on your Google account
2. Go to [App Passwords](https://myaccount.google.com/apppasswords)
3. Select **Mail** → **Windows Computer** → **Generate**
4. Copy the 16-character password (remove spaces when pasting into `mailer.php`)
