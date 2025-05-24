<?php

setcookie('apiKey', '', time() - 36000, '/');
setcookie('isAdmin', '', time() - 36000, '/');
setcookie('productId', '', time() - 36000, '/');


header('Location: home.php');


?>