<?php
    session_name(APP_SESSION_NAME);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>