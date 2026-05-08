
<?php
session_start();
include('config/db.php');

$error = "";
$message = "";

if (isset($_GET['registered'])) {
    $message = "Account created successfully. Please login.";
}

if (isset($_GET['logout'])) {
    $message = "You have been logged out.";
}

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("
    SELECT * FROM Users 
    WHERE username='$username' AND password='$password'
    ");

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid login";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Cricket App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/myapp/style.css?v=10">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-showcase">
            <p class="eyebrow">Match control room</p>
            <h1>Cricket App</h1>
            <p>Manage squads, fixtures, scorebook performances, and rankings from one cricket-ready dashboard.</p>

            <div class="login-highlights">
                <span>Players</span>
                <span>Teams</span>
                <span>Matches</span>
                <span>Rankings</span>
            </div>
        </section>

        <section class="login-card">
            <p class="eyebrow">Back to the crease</p>
            <h2>Sign in</h2>
            <p>Enter your account details to continue.</p>

            <?php if ($error) { ?>
                <div class="message error-message"><?php echo $error; ?></div>
            <?php } ?>

            <?php if ($message) { ?>
                <div class="message"><?php echo $message; ?></div>
            <?php } ?>

            <form method="POST">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" autocomplete="username" required>

                <label for="password">Password</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>

                <input type="submit" value="Login">
            </form>
            <p class="auth-switch">New here? <a href="/myapp/register.php">Create an account</a></p>
        </section>
    </main>
</body>
</html>
