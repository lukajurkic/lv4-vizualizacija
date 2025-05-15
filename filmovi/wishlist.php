<?php
include("../db.php");
include('../header.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$filter_genre = $_GET['genre'] ?? '';
$filter_year = $_GET['year'] ?? '';
$filter_country = $_GET['country'] ?? '';
$sort = $_GET['sort'] ?? 'title';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$genres = mysqli_query($con, "SELECT DISTINCT genre FROM movies WHERE genre != '' ORDER BY genre");
$years = mysqli_query($con, "SELECT DISTINCT year FROM movies ORDER BY year DESC");
$countries = mysqli_query($con, "SELECT DISTINCT country FROM movies WHERE country != '' ORDER BY country");

$allowed_sorts = ['title', 'year', 'genre', 'country', 'avg_vote'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'title';
}

$sql = "SELECT m.* FROM wishlist_movies w 
        JOIN movies m ON w.movie_id = m.filmtv_id 
        WHERE w.user_id = ?";

$params = [$user_id];
$types = "i";

if (!empty($filter_genre)) {
    $sql .= " AND m.genre = ?";
    $params[] = $filter_genre;
    $types .= "s";
}
if (!empty($filter_year)) {
    $sql .= " AND m.year = ?";
    $params[] = $filter_year;
    $types .= "i";
}
if (!empty($filter_country)) {
    $sql .= " AND m.country = ?";
    $params[] = $filter_country;
    $types .= "s";
}

$count_sql = "SELECT COUNT(*) FROM ($sql) as count_table";
$stmt = $con->prepare($count_sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($total_movies);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total_movies / $limit);

$sql .= " ORDER BY m.$sort LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Moja Wishlista</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<h2>Moja Wishlista</h2>

<form method="GET" class="filter-box">
    Žanr:
    <select name="genre">
        <option value="">Svi</option>
        <?php while ($g = mysqli_fetch_assoc($genres)) { ?>
            <option value="<?= htmlspecialchars($g['genre']) ?>" <?= $filter_genre == $g['genre'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($g['genre']) ?>
            </option>
        <?php } ?>
    </select>

    Godina:
    <select name="year">
        <option value="">Sve</option>
        <?php while ($y = mysqli_fetch_assoc($years)) { ?>
            <option value="<?= $y['year'] ?>" <?= $filter_year == $y['year'] ? 'selected' : '' ?>>
                <?= $y['year'] ?>
            </option>
        <?php } ?>
    </select>

    Država:
    <select name="country">
        <option value="">Sve</option>
        <?php while ($c = mysqli_fetch_assoc($countries)) { ?>
            <option value="<?= htmlspecialchars($c['country']) ?>" <?= $filter_country == $c['country'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['country']) ?>
            </option>
        <?php } ?>
    </select>

    Sortiraj po:
    <select name="sort">
        <option value="title" <?= $sort == 'title' ? 'selected' : '' ?>>Naziv</option>
        <option value="year" <?= $sort == 'year' ? 'selected' : '' ?>>Godina</option>
        <option value="genre" <?= $sort == 'genre' ? 'selected' : '' ?>>Žanr</option>
        <option value="country" <?= $sort == 'country' ? 'selected' : '' ?>>Država</option>
        <option value="avg_vote" <?= $sort == 'avg_vote' ? 'selected' : '' ?>>Ocjena</option>
    </select>

    <input type="submit" value="Primijeni">
</form>

<table>
    <tr>
        <th>Naziv</th>
        <th>Godina</th>
        <th>Žanr</th>
        <th>Trajanje</th>
        <th>Država</th>
        <th>Prosječna ocjena</th>
        <th>Ukloni</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= $row['year'] ?></td>
            <td><?= htmlspecialchars($row['genre']) ?></td>
            <td><?= $row['duration'] ?> min</td>
            <td><?= htmlspecialchars($row['country']) ?></td>
            <td><?= htmlspecialchars($row['avg_vote']) ?></td>
            <td>
                <form method="POST" action="remove_from_wishlist.php">
                    <input type="hidden" name="movie_id" value="<?= $row['filmtv_id'] ?>">
                    <button type="submit">Ukloni</button>
                </form>
            </td>
        </tr>
    <?php } ?>
</table>

<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++) {
        $query = $_GET;
        $query['page'] = $i;
        $link = '?' . http_build_query($query);
        ?>
        <a class="<?= $i == $page ? 'current' : '' ?>" href="<?= $link ?>"><?= $i ?></a>
    <?php } ?>
</div>

</body>
</html>
