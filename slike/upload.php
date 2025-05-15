<?php
include("../db.php");
include('../header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $image = $_FILES['image'];

    $allowedFormats = ['image/jpeg', 'image/png'];
    $maxSize = 5 * 1024 * 1024;

    if (!in_array($image['type'], $allowedFormats)) {
        echo "Dozvoljeni formati su JPEG i PNG.";
    } elseif ($image['size'] > $maxSize) {
        echo "Veličina slike ne smije biti veća od 5MB.";
    } else {
        $imagePath = "../uploads/" . uniqid() . "-" . basename($image['name']);
        
        if (move_uploaded_file($image['tmp_name'], $imagePath)) {
            $user_id = $_SESSION['user_id'];
            $query = $con->prepare("INSERT INTO images (title, path, pub_date) VALUES (?, ?, NOW())");
            $query->bind_param("ss", $title, $imagePath);
            $query->execute();

            echo "Slika uspješno dodana!";
        } else {
            echo "Došlo je do pogreške pri spremanju slike.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Dodaj novu sliku</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header></header>

    <h1 class="title">Dodaj novu sliku</h1>

    <div class="container">
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="title">Naziv slike:</label>
            <input type="text" name="title" id="title" required>

            <label for="image">Odaberi sliku:</label>
            <input type="file" name="image" id="image" accept="image/jpeg, image/png" required>
            <input type="submit" value="Dodaj sliku">
        </form>
    </div>
</body>
</html>

