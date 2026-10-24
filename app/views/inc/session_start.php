<?php
session_name(APP_SESSION_NAME);

if (session_status() === PHP_SESSION_NONE) {
    $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 86400, // 24 horas
        'path'     => '/',
        'domain'   => '',
        'secure'   => $esHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}
?>