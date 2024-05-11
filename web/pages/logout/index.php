<?php
require('../index.php');

if (isset($_COOKIE['cookies'])) {
    $cookie = $_COOKIE['cookies'];
    $pdo = connect();

    // Unset cookies and set current user uuid to null
    $sql = "UPDATE profile SET uuid=null WHERE uuid == '$cookie';";
    unset($_COOKIE['cookies']);
    // setcookie('cookies', '', 0, '/');
    setcookie('cookies', '', [
        'expires' => 0,
        'path' => '/',
        'domain' => 'localhost',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
}
header("Location: http://localhost:8080");
?>