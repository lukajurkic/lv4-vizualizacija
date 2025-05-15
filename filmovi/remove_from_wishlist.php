<?php
include("../db.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$movie_id = intval($_POST["movie_id"]);

$stmt = $con->prepare("DELETE FROM wishlist_movies WHERE user_id = ? AND movie_id = ?");
$stmt->bind_param("ii", $user_id, $movie_id);
$stmt->execute();

header("Location: wishlist.php");
exit();
