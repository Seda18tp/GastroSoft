<?php

$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
              (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || 
              (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
              ? "https://" : "http://";

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (str_contains($host, 'localhost') || str_starts_with($host, '127.0.0.1')) {
    define('APP_URL', $protocolo . $host . '/gastrosoft/');
} else {
    define('APP_URL', $protocolo . $host . '/');
}

const APP_NAME = "GastroSoft";
const APP_SESSION_NAME = "GASTROSOFT_SESSION";

date_default_timezone_set("America/Bogota");