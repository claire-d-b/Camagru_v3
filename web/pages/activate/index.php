<?php
    require('../index.php');
    $pdo = connect();

    $sql = 'UPDATE profile SET activated=(?) WHERE activated = (?);';
    $set_act = $pdo->prepare($sql);
    $one = 1;
    $zero = 0;
    $set_act->bindParam(1, $one);
    $set_act->bindParam(2, $zero);
    $set_act->execute();
    header("Location: http://localhost:8080/login/index.php");
?>