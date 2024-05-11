<?php
    require('../index.php');
    $pdo = connect();

    $sql = 'UPDATE profile SET activated=1 WHERE activated = 0;';
    $pdo->prepare($sql)->execute();
    header("Location: http://localhost:8080/login/index.php");
?>