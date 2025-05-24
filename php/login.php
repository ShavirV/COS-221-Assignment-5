<?php
require_once(__DIR__.'/../config.php');
$title = "Login - Compare IT";
$cssFile = "login.css";
require_once 'header.php';
?>

<div class="login-content">
    <div class="login-container">
        <form id="loginForm">
            <div id="errorMessage" class="error-message"></div>
            
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn">LOGIN</button>
        </form>
    </div>
</div>

<script src="../js/login.js"></script>
</body>
</html>