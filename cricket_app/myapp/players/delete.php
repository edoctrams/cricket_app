<?php

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {

    header("Location: view.php");
    exit();
}

include('../config/db.php');

if (!isset($_GET['id'])) {

    header("Location: view.php");
    exit();
}

$id = (int) $_GET['id'];


// Delete related child records first

$conn->query("DELETE FROM Performance WHERE player_id = $id");

$conn->query("DELETE FROM Ranking WHERE player_id = $id");

$conn->query("DELETE FROM Plays_For WHERE player_id = $id");


// Delete player

$conn->query("DELETE FROM Player WHERE player_id = $id");


// Redirect back automatically

header("Location: view.php?deleted=1");

exit();

?>