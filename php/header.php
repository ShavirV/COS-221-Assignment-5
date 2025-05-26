<?php
//session_start();

/*echo "<!-- COOKIE DEBUG START -->\n";
echo "<!-- api_key exists: " . (isset($_COOKIE['api_key']) ? 'YES' : 'NO') . " -->\n";
echo "<!-- user_email exists: " . (isset($_COOKIE['user_email']) ? 'YES (' . htmlspecialchars($_COOKIE['user_email']) . ')' : 'NO') . " -->\n";
echo "<!-- All cookies: " . print_r($_COOKIE, true) . " -->\n";
echo "<!-- COOKIE DEBUG END -->\n";
*/

$loggedIn = false;
$isAdmin = false;
$userEmail = '';

// checks if api key cookie exists & is not empty
if (isset($_COOKIE['api_key']) && !empty($_COOKIE['api_key'])) {
    $loggedIn = true;
    $isAdmin = (isset($_COOKIE['isAdmin']) && $_COOKIE['isAdmin'] === 'true');
    $userEmail = $_COOKIE['user_email'] ?? 'User'; // Get the email if it exists
    echo "<!-- DEBUG: Using email: " . htmlspecialchars($userEmail) . " -->";
}
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
  background-color: rgb(117, 155, 225);
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

.user-email {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255, 255, 255, 0.8);
    padding: 5px 10px;
    font-size: 0.9rem;
    margin-left: 10px;
}

.user-email i {
    font-size: 1rem;
}

.user-email:hover {
    color: white;
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.7);
}

/* Adjust logout button spacing */
.nav-links li:has(+ .user-email) a {
    margin-right: 5px;
}
/* Light mode critical styles */
    body.light-mode {
        color: #333;
        background: #f5f5f5;
    }
    
    body.light-mode .background-overlay {
        background-image: url("../img/glow.jpg");
        background-color: #f5f5f5;
    }
    
    body.light-mode .top-navbar {
        background-color: rgba(255, 255, 255, 0.2);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    body.light-mode .top-navbar .brand,
    body.light-mode .top-navbar .nav-links li a {
        color: #333;
    }
     body.light-mode .top-navbar .nav-links li a:hover {
        color:rgb(104, 132, 188);
        text-shadow: none;
    }
    
    body.light-mode .top-navbar .nav-links li a.active {
        background-color:rgb(100, 136, 214);
        color: white;
        text-shadow: none;
        box-shadow: 0 0 10px rgba(20, 45, 93, 0.95);
    }
    /* Light mode styles for footer */
body.light-mode .site-footer {
  background: linear-gradient(135deg, #f5f5f5 0%, #e9e9e9 100%);
  color: #222;
}

body.light-mode .footer-wave {
  background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="%23f5f5f5" opacity=".25"/><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" fill="%23f5f5f5" opacity=".5"/><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="%23f5f5f5"/></svg>');
  background-size: cover;
  background-color: #f5f5f5;
}

body.light-mode .footer-title {
  background: linear-gradient(to right, #6884bc, #2541b2);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

body.light-mode .footer-column h3,
body.light-mode .footer-column h3::after {
  color:rgb(0, 0, 0);
}

body.light-mode .footer-column ul li a {
  color: #333;
}

body.light-mode .footer-column ul li a:hover {
  color: #6884bc;
}

body.light-mode .footer-column ul li a::after {
  background-color: #6884bc;
}

body.light-mode .social-icon {
  color: #333;
  background: rgba(0,0,0,0.05);
}

body.light-mode .social-icon:hover {
  background: #6884bc;
  color: #fff;
}

body.light-mode .newsletter-form input {
  background: rgba(0,0,0,0.05);
  color: #222;
}

body.light-mode .newsletter-form input::placeholder {
  color: #888;
}

body.light-mode .newsletter-form button {
  color: #fff;
}

body.light-mode .footer-bottom,
body.light-mode .footer-legal a {
  color: #888;
  border-top: 1px solid #e0e0e0;
}

body.light-mode .footer-legal a:hover {
  color: #6884bc;
}

body.light-mode #scrollToTop {
  background: linear-gradient(to right, #6884bc, #2541b2);
  color: #fff;
}

    </style>
    <script src="../js/theme.js" defer></script>
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
            
            <?php if (isset($_COOKIE['api_key'])): ?>
                <li><a href="logout.php">LOGOUT</a></li>
            <?php else: ?>
                <li><a href="login.php" <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'class="active"' : ''; ?>>LOGIN</a></li>
                <li><a href="signup.php" <?php echo basename($_SERVER['PHP_SELF']) == 'signup.php' ? 'class="active"' : ''; ?>>SIGN UP</a></li>
            <?php endif; ?>
            <li>
              <button id="theme-toggle" class="theme-toggle">
              <i class="fas fa-moon"></i>
              </button>
            </li>
        </ul>
    </nav>
