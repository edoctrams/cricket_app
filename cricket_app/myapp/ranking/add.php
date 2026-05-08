<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}
?>
<?php include('../header.php'); ?>
<?php
include('../config/db.php');

// Fetch players
$players = $conn->query("SELECT * FROM Player");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $player_id = $_POST["player_id"];
    $format = $_POST["format"];
    $position = $_POST["position"];

    $conn->query("INSERT INTO Ranking (player_id, format, rank_position)
                  VALUES ($player_id, '$format', $position)");

    header("Location: view.php");
}
?>

<hr>
<a href="../index.php">Home</a> |
<a href="add.php">Add Ranking</a> |
<a href="view.php">View Rankings</a>
<hr>

<h2>Add Ranking</h2>

<form method="POST">

Player:<br>
<select name="player_id">
<?php while($p = $players->fetch_assoc()) { ?>
    <option value="<?php echo $p['player_id']; ?>">
        <?php echo $p['name']; ?>
    </option>
<?php } ?>
</select><br><br>

Format:<br>
<select name="format">
    <option value="ODI">ODI</option>
    <option value="T20">T20</option>
    <option value="Test">Test</option>
</select><br><br>

Rank Position:<br>
<input type="number" name="position"><br><br>

<input type="submit" value="Add Ranking">

</form>
<?php include('../footer.php'); ?>
