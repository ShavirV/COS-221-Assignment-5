<?php

setcookie('apiKey', '', time() - 3600, '/');
setcookie('isAdmin', '', time() - 3600, '/');


header('Location: home.php');


?>