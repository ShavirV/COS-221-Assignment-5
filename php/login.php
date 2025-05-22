<?php
session_start();
require_once(__DIR__.'/../config.php');?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>COMPARE IT - login</title>
    <link rel="stylesheet" href="../css/login.css" />
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
                <li><a href="home.php">HOME</a></li>
                <li><a href="products.php">PRODUCTS</a></li>
                <li><a href="wishlist.php">WISHLIST</a></li>
                <li><a href="aboutUs.php">ABOUT US</a></li>
                <li><a href="login.php" class="active">LOGIN</a></li>
                <li><a href="signup.php">SIGN UP</a></li>
            </ul>
        </nav>

        <div class="login-container">

            <form id="loginForm">
                <div id="errorMessage" class="error-message"></div>
                
                <div class="input-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        required
                    />
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    />
                </div>

                <div class="user-type">
                    <div class="user-option">
                        <input
                            type="radio"
                            id="customer"
                            name="userType"
                            value="customer"
                            checked
                        />
                        <label for="customer">Customer</label>
                    </div>
                    <div class="user-option">
                        <input type="radio" id="admin" name="userType" value="admin" />
                        <label for="admin">Admin</label>
                    </div>
                </div>

                <button type="submit" class="login-btn">LOGIN</button>
            </form>
        </div>
    </div>

    <script src="../js/login.js"></script>
</body>
</html>