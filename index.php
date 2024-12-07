<?php
session_start();

// Kullanıcı oturum kontrolü
if (isset($_SESSION['user_id'])) {
    // Kullanıcı giriş yapmışsa dashboard'a yönlendir
    header("Location: dashboard.php");
    exit;
} else {
    // Kullanıcı giriş yapmamışsa login sayfasına yönlendir
    header("Location: login.php");
    exit;
}
?>
