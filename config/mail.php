<?php
// EMAIL (PHPMailer / SMTP)
// Keep real credentials in server environment variables, not in source code.
define('MAIL_ENABLED',   filter_var(getenv('MAIL_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN));
define('MAIL_HOST',      getenv('MAIL_HOST') ?: 'smtp.hostinger.com');
define('MAIL_PORT',      (int) (getenv('MAIL_PORT') ?: 465));
define('MAIL_USERNAME',  getenv('MAIL_USERNAME') ?: '');
define('MAIL_PASSWORD',  getenv('MAIL_PASSWORD') ?: '');
define('MAIL_FROM',      getenv('MAIL_FROM') ?: (MAIL_USERNAME ?: 'no-reply@localhost'));
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: APP_NAME);