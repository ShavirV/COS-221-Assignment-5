<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home - Compare IT</title>
    <link rel="stylesheet" href="../css/home.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
  </head>
  <body>
    <div class="background-overlay"></div>

    <!-- Top Navigation Bar -->
    <nav class="top-navbar">
      <div class="brand">
        <img src="../img/logo.jpg" alt="Compare IT Logo" class="logo" />
        <span>Compare IT</span>
      </div>
      <ul class="nav-links">
        <li><a href="home.php" class="active">HOME</a></li>
        <li><a href="products.php">PRODUCTS</a></li>
        <li><a href="wishlist.php">WISHLIST</a></li>
        <li><a href="aboutUs.php">ABOUT US</a></li>
        <li><a href="login.php">LOGIN</a></li>
        <li><a href="signup.php">SIGN UP</a></li>
      </ul>
    </nav>

    <!-- Rest of your home page content would go here -->
    <script src="../js/home.js"></script>
  </body>
</html>