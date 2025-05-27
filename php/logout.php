<?php
session_start();

setcookie('api_key', '', time() - 3600, '/');
setcookie('isAdmin', '', time() - 3600, '/');
setcookie('productId', '', time() - 3600, '/');
setcookie('user_email', '', time() - 3600, '/');

// destroy the session* NB!!!
session_destroy();

// go to home page
header('Location: home.php');
exit();
?>
