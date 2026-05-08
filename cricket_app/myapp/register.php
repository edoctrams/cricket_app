<?php
session_start();
include('config/db.php');

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $confirm_password = $_POST["confirm_password"];

    if ($username === "" || $password === "") {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT * FROM Users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $existing = $check->get_result();

        if ($existing->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $role = "user";
            $stmt = $conn->prepare("INSERT INTO Users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $role);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register | Cricket App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/myapp/style.css?v=10">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-showcase">
            <p class="eyebrow">Join the pavilion</p>
            <h1>Cricket App</h1>
            <p>Create a regular user account to view squads, fixtures, performance scorecards, and rankings.</p>

            <div class="login-highlights">
                <span>View data</span>
                <span>No admin tools</span>
                <span>Simple access</span>
            </div>
        </section>

        <section class="login-card">
            <p class="eyebrow">New spectator account</p>
            <h2>Register</h2>
            <p>Regular users can view records but cannot add, edit, or delete data.</p>

            <?php if ($error) { ?>
                <div class="message error-message"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" autocomplete="username" required>

                <label for="password">Password</label>
                <input id="password" type="password" name="password" autocomplete="new-password" required>

                <label for="confirm_password">Confirm Password</label>
                <input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" required>

                <input type="submit" value="Create Account">
            </form>
            <p class="auth-switch">Already have an account? <a href="/myapp/login.php">Sign in</a></p>
        </section>
    </main>
</body>
</html>
