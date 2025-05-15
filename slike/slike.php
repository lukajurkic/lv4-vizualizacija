<?php  
include("../db.php");
include("../header.php");

$images = $con->query("SELECT images.id, images.title, images.path,
    COALESCE(ROUND(AVG(ratings.rating), 2), 0) AS prosjecna_ocjena
    FROM images
    LEFT JOIN ratings ON images.id = ratings.image_id
    GROUP BY images.id
    ORDER BY images.pub_date DESC");
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Galerija slika">
    <meta name="author" content="Ante Dragun">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/images_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css">
    <title>Galerija slika</title>
</head>
<body>

<h1 class="title">Galerija slika</h1>

<div class="img-gallery-magnific">
    <?php while ($image = $images->fetch_assoc()): ?>
        <figure class="galerija_slika">
    <div class="slika-wrapper">
        <a class="image-popup-vertical-fit" href="<?= htmlspecialchars($image['path']) ?>" title="<?= htmlspecialchars($image['title']) ?>">
            <img src="<?= htmlspecialchars($image['path']) ?>" alt="<?= htmlspecialchars($image['title']) ?>" loading="lazy">
            <div class="overlay">
                <div class="text"><?= htmlspecialchars($image['title']) ?></div>
            </div>
        </a>
    </div>

        <div class="avg-rating">Prosječna ocjena: <?= number_format($image['prosjecna_ocjena'], 1) ?> ★</div>
        <div class="rating-stars">
            <form action="./rate.php" method="post">
                <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button name="rating" value="<?= $i ?>" class="star-btn">★</button>
                <?php endfor; ?>
            </form>
        </div>
</figure>

    <?php endwhile; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js"></script>
<script>
    $(document).ready(function() {
        $('.img-gallery-magnific').magnificPopup({
            delegate: 'a.image-popup-vertical-fit',
            type: 'image',
            gallery: {
                enabled: true
            }
        });
    });
</script>

</body>
</html>
