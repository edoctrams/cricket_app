<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}
?>
<?php include('../header.php'); ?>
<?php
include('../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $venue = $_POST["venue"];
    $date = $_POST["match_date"];
    $format = $_POST["format"];
    $match_type = $_POST["match_type"];

    $conn->query("INSERT INTO Match_Details (venue, match_date, format, match_type)
VALUES ('$venue', '$date', '$format', '$match_type')");

    header("Location: view.php");
}
?>

<hr>
<a href="../index.php">Home</a> |
<a href="add.php">Add Match</a> |
<a href="view.php">View Matches</a>
<hr>

<h2>Add Match</h2>

<form method="POST">
    Venue:<br>
    <input type="text" name="venue"><br><br>

    Date:<br>
    <input type="date" name="match_date"><br><br>

    Format:<br>
    <input type="text" name="format"><br><br>

    Match Type:<br>
    <select name="match_type">
        <option value="International">International</option>
        <option value="Domestic">Domestic</option>
    </select><br><br>

    <input type="submit" value="Add Match">
</form>
<?php include('../footer.php'); ?>
