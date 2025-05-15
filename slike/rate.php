<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

if (isset($_POST['rating']) && isset($_POST['image_id'])) {
    $user_id = $_SESSION['user_id'];
    $image_id = $_POST['image_id'];
    $rating = $_POST['rating'];

    if ($rating >= 1 && $rating <= 5) {
        $checkRating = $con->prepare("SELECT * FROM ratings WHERE user_id = ? AND image_id = ?");
        $checkRating->bind_param("ii", $user_id, $image_id);
        $checkRating->execute();
        $result = $checkRating->get_result();

        if ($result->num_rows > 0) {
            $updateRating = $con->prepare("UPDATE ratings SET rating = ? WHERE user_id = ? AND image_id = ?");
            $updateRating->bind_param("iii", $rating, $user_id, $image_id);
            $updateRating->execute();
        } else {
            $insertRating = $con->prepare("INSERT INTO ratings (user_id, image_id, rating, pub_date) VALUES (?, ?, ?, NOW())");
            $insertRating->bind_param("iii", $user_id, $image_id, $rating);
            $insertRating->execute();
        }
        header("Location: slike.php");
        exit();
    } else {
        echo "Ocjena mora biti između 1 i 5.";
    }
} else {
    echo "Neispravni podaci.";
}
?>
