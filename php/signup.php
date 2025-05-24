<?php

require_once(__DIR__.'/config.php');
$title = "Signup - Compare IT";
$cssFile = "signup.css"; // CSS file for signup page
require_once 'header.php';
?>


        <div class="signup-container">

            <form id="signupForm">
                <!-- <div id="errorMessage" class="error-message"></div> -->
                
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