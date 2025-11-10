<?php
// filepath: /home/aluzardo/code/docker/php/luzardo/logout.php
session_start();
$_SESSION = [];
session_destroy();
header('Location: ./../index.html');
exit;