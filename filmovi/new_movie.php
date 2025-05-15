<?php
include("../db.php");
include('../header.php');

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$genre_result = mysqli_query($con, "SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL AND genre != '' ORDER BY genre ASC");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $year = intval($_POST["year"]);
    $duration = intval($_POST["duration"]);
    $genre = $_POST["genre"];
    $country = $_POST["country"];

    if (empty($title) || $year < 1880 || $year > date("Y") || $duration <= 0) {
        $message = "Please provide valid title, year (1880–current), and duration.";
    } else {
        $stmt = mysqli_prepare($con, "INSERT INTO movies
            (title, year, genre, duration, country, directors, actors, avg_vote, critics_vote, public_vote, total_votes, description, notes, humor, rhythm, effort, tension, erotism) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        mysqli_stmt_bind_param($stmt, "sisisssddddssiiiii",
            $title,
            $year,
            $genre,
            $duration,
            $country,
            $_POST["directors"],
            $_POST["actors"],
            $_POST["avg_vote"],
            $_POST["critics_vote"],
            $_POST["public_vote"],
            $_POST["total_votes"],
            $_POST["description"],
            $_POST["notes"],
            $_POST["humor"],
            $_POST["rhythm"],
            $_POST["effort"],
            $_POST["tension"],
            $_POST["erotism"]
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Movie added successfully.";
        } else {
            $message = "Error: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dodaj film</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Dodaj novi film</h2>
    <form method="POST" action="">
        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Year:</label>
        <input type="number" name="year" required>

        <label>Genre:</label>
        <select name="genre" required>
            <option value="">-- Select Genre --</option>
            <?php while ($row = mysqli_fetch_assoc($genre_result)): ?>
                <option value="<?php echo htmlspecialchars($row['genre']); ?>">
                    <?php echo htmlspecialchars($row['genre']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Duration (min):</label>
        <input type="number" name="duration">

        <label>Country:</label>
        <input type="text" name="country">

        <label>Directors:</label>
        <input type="text" name="directors">

        <label>Actors:</label>
        <input type="text" name="actors">

        <label>Avg Vote:</label>
        <input type="number" step="0.1" name="avg_vote">

        <label>Critics Vote:</label>
        <input type="number" step="0.1" name="critics_vote">

        <label>Public Vote:</label>
        <input type="number" step="0.1" name="public_vote">

        <label>Total Votes:</label>
        <input type="number" name="total_votes">

        <label>Description:</label>
        <textarea name="description"></textarea>

        <label>Notes:</label>
        <textarea name="notes"></textarea>

        <label>Humor (0–5):</label>
        <input type="number" name="humor" min="0" max="5">

        <label>Rhythm (0–5):</label>
        <input type="number" name="rhythm" min="0" max="5">

        <label>Effort (0–5):</label>
        <input type="number" name="effort" min="0" max="5">

        <label>Tension (0–5):</label>
        <input type="number" name="tension" min="0" max="5">

        <label>Erotism (0–5):</label>
        <input type="number" name="erotism" min="0" max="5">

        <input type="submit" value="Add Movie">
    </form>
    <?php if (!empty($message)): ?>
        <p class="<?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>
</div>
</body>
</html>
