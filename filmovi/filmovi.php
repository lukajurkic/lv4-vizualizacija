<?php
include("../db.php");
include('../header.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$wishlist_ids = [];

$wishlist_q = mysqli_query($con, "SELECT movie_id FROM wishlist_movies WHERE user_id = $user_id");
while ($row = mysqli_fetch_assoc($wishlist_q)) {
    $wishlist_ids[] = $row['movie_id'];
}

$genres = mysqli_query($con, "SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL AND genre != '' ORDER BY genre");
$years = mysqli_query($con, "SELECT DISTINCT year FROM movies ORDER BY year DESC");
$countries = mysqli_query($con, "SELECT DISTINCT country FROM movies WHERE country IS NOT NULL AND country != '' ORDER BY country");

$filter_genre = $_GET['genre'] ?? '';
$filter_year = $_GET['year'] ?? '';
$filter_country = $_GET['country'] ?? '';
$sort = $_GET['sort'] ?? 'title';

$records_per_page = 20;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

$count_sql = "SELECT COUNT(*) FROM movies WHERE 1=1";
if (!empty($filter_genre)) $count_sql .= " AND genre = '" . mysqli_real_escape_string($con, $filter_genre) . "'";
if (!empty($filter_year)) $count_sql .= " AND year = " . intval($filter_year);
if (!empty($filter_country)) $count_sql .= " AND country = '" . mysqli_real_escape_string($con, $filter_country) . "'";
$total_records = mysqli_fetch_array(mysqli_query($con, $count_sql))[0];
$total_pages = ceil($total_records / $records_per_page);

$sql = "SELECT * FROM movies WHERE 1=1";
if (!empty($filter_genre)) $sql .= " AND genre = '" . mysqli_real_escape_string($con, $filter_genre) . "'";
if (!empty($filter_year)) $sql .= " AND year = " . intval($filter_year);
if (!empty($filter_country)) $sql .= " AND country = '" . mysqli_real_escape_string($con, $filter_country) . "'";

$allowed_sorts = ['title', 'year', 'genre', 'country', 'avg_vote'];
if (!in_array($sort, $allowed_sorts)) $sort = 'title';

$sql .= " ORDER BY $sort LIMIT $records_per_page OFFSET $offset";
$result = mysqli_query($con, $sql);

$filter_params = '';
if (!empty($filter_genre)) $filter_params .= '&genre=' . urlencode($filter_genre);
if (!empty($filter_year)) $filter_params .= '&year=' . urlencode($filter_year);
if (!empty($filter_country)) $filter_params .= '&country=' . urlencode($filter_country);
if (!empty($sort)) $filter_params .= '&sort=' . urlencode($sort);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Filmovi</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<h2>Popis filmova</h2>

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
        <option value="avg_vote" <?= $sort == 'avg_vote' ? 'selected' : '' ?>>Prosječna ocjena</option>
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
        <th>Posudi</th>
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
                <?php if (in_array($row['filmtv_id'], $wishlist_ids)) { ?>
                    <span>U wishlisti</span>
                <?php } else { ?>
                    <form method="POST" action="add_to_wishlist.php">
                        <input type="hidden" name="movie_id" value="<?= $row['filmtv_id'] ?>">
                        <button type="submit">Dodaj u wishlist</button>
                    </form>
                <?php } ?>
            </td>

        </tr>
    <?php } ?>
</table>

<div class="pagination">
    <?php if ($current_page > 1): ?>
        <a href="?page=1<?= $filter_params ?>">Prva</a>
        <a href="?page=<?= $current_page - 1 ?><?= $filter_params ?>">Prethodna</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?><?= $filter_params ?>" <?= $i == $current_page ? 'class="active"' : '' ?>><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($current_page < $total_pages): ?>
        <a href="?page=<?= $current_page + 1 ?><?= $filter_params ?>">Sljedeća</a>
        <a href="?page=<?= $total_pages ?><?= $filter_params ?>">Zadnja</a>
    <?php endif; ?>
</div>

</body>
</html>
