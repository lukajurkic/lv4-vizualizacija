<?php
session_start();
?>

<head>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>

<body>
    <div class="header">
        <nav>
            <ul class="nav-list">
                <?php if (isset($_SESSION["username"])): ?>
                    <li><a href="../index.html">Pocetna</a></li>
                    <li><a href="../lv4.php">Pocetna(LV4)</a></li>
                    <li><a href="/filmovi/filmovi.php">Filmovi</a></li>
                    <li><a href="/filmovi/wishlist.php">Wishlist</a></li>
                    <li><a href="/slike/slike.php">Slike</a></li>
                    <li><a href="/slike/upload.php">Dodaj sliku</a></li>

                    <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                        <li><a href="/filmovi/new_movie.php">Dodaj film</a></li>
                    <?php endif; ?>

                    <li><a href="/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/login.php">Login</a></li>
                    <li><a href="/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</body>
