<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: /myapp/login.php");
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cricket App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/myapp/style.css?v=10">
</head>
<body>
<?php include(__DIR__ . '/navbar.php'); ?>
<main class="page-shell">
