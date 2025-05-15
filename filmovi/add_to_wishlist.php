<?php
include("../db.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$movie_id = intval($_POST["movie_id"]);
$confirm_low_rating = $_POST["confirm_low_rating"] ?? false;

$movie_res = $con->prepare("SELECT * FROM movies WHERE filmtv_id = ?");
$movie_res->bind_param("i", $movie_id);
$movie_res->execute();
$movie = $movie_res->get_result()->fetch_assoc();

if (!$movie) {
    die("Film nije pronađen.");
}

$check = $con->prepare("SELECT * FROM wishlist_movies WHERE user_id = ? AND movie_id = ?");
$check->bind_param("ii", $user_id, $movie_id);
$check->execute();
$exists = $check->get_result()->num_rows > 0;

if ($exists) {
    header("Location: filmovi.php");
    exit();
}
if ($movie['avg_vote'] < 5.0 && !$confirm_low_rating) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Upozorenje</title>
        <style>
            .warning-box {
                background-color: #ffe5e5;
                color: #b30000;
                padding: 20px;
                border: 1px solid #b30000;
                border-radius: 5px;
                margin: 30px auto;
                width: 60%;
                font-family: Arial;
            }

            button {
                padding: 8px 15px;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="warning-box">
            <h3>Upozorenje</h3>
            <p>Ovaj film "<strong><?= htmlspecialchars($movie['title']) ?></strong>" ima nisku ocjenu (<?= $movie['avg_vote'] ?>).</p>
            <p>Jeste li sigurni da ga želite dodati u svoju wishlistu?</p>
            <form method="POST" action="add_to_wishlist.php">
                <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
                <input type="hidden" name="confirm_low_rating" value="1">
                <button type="submit">Da, dodaj u wishlistu</button>
                <button type="button" onclick="window.location.href='filmovi.php'">Ne, natrag</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$stmt = $con->prepare("INSERT INTO wishlist_movies (user_id, movie_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $movie_id);
$stmt->execute();

header("Location: filmovi.php");
exit();
