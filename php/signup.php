<?php
session_start();
require_once(__DIR__.'/config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up - Compare IT</title>
    <link rel="stylesheet" href="../css/signup.css" />
    <style>
        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="background-image"></div>
    <div class="content">
        <nav class="navbar">
            <div class="brand">
                <img src="../img/logo.jpg" alt="Compare IT Logo" class="logo" />
                Compare IT
            </div>
            <ul>
                <li><a href="home.html">HOME</a></li>
                <li><a href="products.html">PRODUCTS</a></li>
                <li><a href="wishlist.html">WISHLIST</a></li>
                <li><a href="aboutUs.html">ABOUT US</a></li>
                <li><a href="login.php">LOGIN</a></li>
                <li><a href="signup.php" class="active">SIGN UP</a></li>
            </ul>
        </nav>

        <div class="signup-container">

            <form id="signupForm">
                <div id="errorMessage" class="error-message"></div>
                
                <div class="name-fields">
                    <div class="input-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" placeholder="First name" required />
                    </div>
                    <div class="input-group">
                        <label for="surname">Surname</label>
                        <input type="text" id="surname" name="surname" placeholder="Last name" required />
                    </div>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required />
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required />
                    <div class="password-hint">
                        Must contain: 8+ characters, 1 uppercase, 1 lowercase, 1 number
                            </div>
                </div>

                <div class="user-type">
                    <div class="user-option">
                        <input type="radio" id="admin" name="accountType" value="admin" checked />
                        <label for="admin">Admin</label>
                    </div>
                    <div class="user-option">
                        <input type="radio" id="customer" name="accountType" value="customer" />
                        <label for="customer">Customer</label>
                    </div>
                </div>

                <button type="submit" class="signup-btn">SIGN UP</button>

                <div class="login-link">
                    Already have an account? <a href="login.php">Log in</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/signup.js"></script>
</body>
</html>