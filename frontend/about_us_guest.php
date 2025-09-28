<?php

require '../backend/db.php';
?>
<!DOCTYPE html>
<html lang="hu">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rólunk - UGRÁ-LÓ</title>
  <link rel="stylesheet" href="../frontend/styles.css">
</head>

<body>
  <div class="background">
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
    <span class="ball"></span>
  </div>
  <header>
    <div class="logo-container">
      <img src="../frontend/img/logo.png" class="logo" alt="Ugráló Logó">
    </div>
    <div class="header-content">
      <h1>UGRÁ-LÓ</h1>
      <nav>
        <a href="guest.php">Vissza</a>
        <a href="login.php"><span>🚪</span> Bejelentkezés</a>
        <a href="register.php">Regisztráció</a>
        <a href="about_us_guest.php" class="active">Rólunk</a>
      </nav>
    </div>
  </header>
  </header>

  <main>
    <h1 class="about_us_btn">Rólunk:</h1>
    <div>
      <p class="content">
        1992-óta foglalkozunk gyermekszórakoztatással. Elsődleges szempontunk a kicsik mozgásigényének kielégítése és izgalmas játékok kipróbáltatása.
        Az országban az elsők között népszerűsítettük a felfújható játékokat a gyermekek körében. Azóta a megrendelők igényeit figyelembe véve folyamatosan bővítjük játékaink kínálatát.
        Vállaljuk mobil -és fix játszóházak tervezését és teljeskörű kivitelezését utánfutókra, teherautókra és épületekbe, egyéni ötletek alapján is.
        Rendezvényeinken légvár, játszóház, körhinta, kalandpark, pónilovaglás és sok más érdekes szabadtéri játék várja a szórakozni vágyó gyermekeket és szüleiket!
        Eszközeink korszerűek, gyorsan telepíthetők, kiváló minőségű anyagból készülnek, és a szigorú ÉMI-Bayer-TÜV vizsgával rendelkeznek, így megfelelnek az EU biztonsági előírásainak!
      </p>
    </div>
    <h1 class="about_us_btn">
      Elérhetőségeink:
    </h1>
    <div class="content">
      <h2>Cím:</h2>Törökszentmiklós, Széchenyi István út 28, 5200<br><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2712.082958028695!2d20.422803076372514!3d47.17581231762632!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x474149c6c9c2f357%3A0x4e308ebbadc5f946!2zVUdSw4EtTMOT!5e0!3m2!1shu!2shu!4v1755936504762!5m2!1shu!2shu" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      <h2>Telefonszám:</h2>06 20 918 6224 / 06 56 393 421
      <h2>Email:</h2>ugra-lo@freemail.hu
    </div>
  </main>
</body>

</html>