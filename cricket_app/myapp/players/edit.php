<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: view.php");
    exit();
}
?>
<?php include('../header.php'); ?>
<hr>
<a href="../index.php">Home</a> |
<a href="add.php">Add Player</a> |
<a href="view.php">View Players</a>
<hr>
<?php
include('../config/db.php');

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $country = $_POST["country"];
    $role = $_POST["role"];

    $conn->query("UPDATE Player 
                  SET name='$name', country='$country', role='$role'
                  WHERE player_id=$id");

    header("Location: view.php");
}

// Fetch existing data
$result = $conn->query("SELECT * FROM Player WHERE player_id=$id");
$row = $result->fetch_assoc();
?>

<h2>Edit Player</h2>

<form method="POST">
    Name: <input type="text" name="name" value="<?php echo $row['name']; ?>"><br>
    Country: <input type="text" name="country" value="<?php echo $row['country']; ?>"><br>
    Role: <input type="text" name="role" value="<?php echo $row['role']; ?>"><br>
    <input type="submit" value="Update">
</form>
<?php include('../footer.php'); ?>
