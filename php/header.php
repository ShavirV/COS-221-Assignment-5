<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Compare IT'; ?></title>
    <?php if (isset($cssFile)): ?>
        <link rel="stylesheet" href="../css/<?php echo $cssFile; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Critical CSS that must load immediately */
        * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Arial", sans-serif;
}

body {
  overflow-x: hidden;
  color: white;
  background-attachment: fixed;
}

/* Background Overlay */
.background-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: url("../img/background.jpg");
  background-repeat: no-repeat;
  background-size: cover;
  z-index: -1;
}

/* Reuse navigation styles from wishlist.css */
.top-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 50px;
  background-color: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(5px);
  position: sticky;
  top: 0;
  z-index: 100;
}

.top-navbar .brand {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  text-transform: uppercase;
}

.top-navbar .logo {
  height: 50px;
  width: auto;
}

.top-navbar .nav-links {
  display: flex;
  list-style: none;
  gap: 25px;
}

.top-navbar .nav-links li a {
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  text-transform: uppercase;
  font-size: 0.9rem;
  letter-spacing: 1px;
  padding: 5px 10px;
  transition: all 0.3s ease;
  position: relative;
  display: inline-block;
}

.top-navbar .nav-links li a:hover {
  color: white;
  text-shadow: 0 0 15px rgba(255, 255, 255, 1),
    0 0 25px rgba(255, 255, 255, 0.8);
  transform: translateY(-3px);
}

.top-navbar .nav-links li a.active {
  color: white;
  background-color: rgb(22, 46, 90);
  border-radius: 3px;
  text-shadow: 0 0 15px rgba(255, 255, 255, 1),
    0 0 25px rgba(255, 255, 255, 0.8);
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.4),
    0 0 25px rgba(255, 255, 255, 0.5), 0 4px 8px rgba(255, 255, 255, 0.3);
}
.top-navbar .nav-links li a::after {
  content: "";
  position: absolute;
  width: 100%;
  height: 2px;
  bottom: 0;
  left: 0;
  background-color: white;
  transform: scaleX(0);
  transition: transform 0.3s ease;
}
.top-navbar .nav-links li a:hover::after {
  transform: scaleX(1);
}
    </style>
</head>
<body>
    <!-- Always show background -->
    <div class="background-overlay"></div>
    
    <!-- Always show navbar -->
    <nav class="top-navbar">
        <div class="brand">
            <img src="../img/logo.jpg" alt="Compare IT Logo" class="logo">
            <span>Compare IT</span>
        </div>
        <ul class="nav-links">
            <li><a href="home.php" <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'class="active"' : ''; ?>>HOME</a></li>
            <li><a href="products.php" <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : ''; ?>>PRODUCTS</a></li>
            <li><a href="wishlist.php" <?php echo basename($_SERVER['PHP_SELF']) == 'wishlist.php' ? 'class="active"' : ''; ?>>WISHLIST</a></li>
            <li><a href="aboutUs.php" <?php echo basename($_SERVER['PHP_SELF']) == 'aboutUs.php' ? 'class="active"' : ''; ?>>ABOUT US</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="logout.php">LOGOUT</a></li>
            <?php else: ?>
                <li><a href="login.php" <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'class="active"' : ''; ?>>LOGIN</a></li>
                <li><a href="signup.php" <?php echo basename($_SERVER['PHP_SELF']) == 'signup.php' ? 'class="active"' : ''; ?>>SIGN UP</a></li>
            <?php endif; ?>
        </ul>
    </nav>