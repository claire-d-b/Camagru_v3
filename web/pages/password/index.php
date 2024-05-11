<?php
    require ('../index.php');

    logout();

    $uuid = $_COOKIE["cookies"];

    $pdo = connect();
    $print = false;
    $start = false;

    $sql = "SELECT * FROM profile WHERE uuid = '$uuid'";
    $data = $pdo->prepare($sql);
    $data->execute();
    $get_profile = $data->fetch(\PDO::FETCH_ASSOC);
    $name = $get_profile['firstname'];
    $surname = $get_profile['surname'];
    $mail = $get_profile['username'];
    $validpass = $get_profile['validpass'];

    // Change password when it has been forgotten. Not asking old one.
    $update_profile = '';
    $pass = $_POST['change_pwd1'];
    if (isset($_POST['change_pwd2']) && isset($_POST['change_pwd1']) &&
    ctype_space($_POST['change_pwd2']) == false && strcmp($_POST['change_pwd2'], '')
    && ctype_space($_POST['change_pwd1']) == false && strcmp($_POST['change_pwd1'], '')
    && (!(strcmp($_POST['change_pwd1'], $_POST['change_pwd2'])))
    && validate_input($_POST['change_pwd1'], 2) == true) {
      $new = password_hash($pass, PASSWORD_BCRYPT);
      $update_profile = "UPDATE profile SET validpass='$new' WHERE uuid = '$uuid'";
      $pdo->prepare($update_profile)->execute();
      $print = false;
      // header("Location: http://localhost:8080/login/index.php");
    }
    else $print = true;
    else if (isset($_POST['change_pwd2']) || isset($_POST['change_pwd1']))
        $print = true;
    else
      $start = true;
?>

<html dir='ltr' lang='en' style='font-family: system-ui; margin: 0; height: 100%; width: 100%;'>
  <head>
    <div style='display: flex; justify-content: center; align-items: center; width: 100%; background: white; height: 40px; position: fixed; top: 0px; box-shadow: 0px 2px 2px #808b96;'>
      <meta charset='utf-8'>
      <div style='display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 20px;'>
        <div style='display: flex; gap: 0.5em;'></div>
        <div style="font-size: 18px; color: #7393B3; width: 100%; text-align: right; padding-right: 15px;">Camagru</div>
      </div>
    </div>
  </head>
  <body style='margin: 0; font-family: system-ui; width: 100%; height: 100%; overflow: hidden;'>
    <form action='index.php' method='post' style='color: #7393B3; font-family: system-ui; display: flex; width: 100%; height: 100%; justify-content: center; align-items: center;'>
        <div style='width: 100%; height: 100%; margin-right: 25px; background: repeat center/10% url(../src/images/cover.png);'></div>
        <div style='display: flex; flex-direction: column; gap: 0.5em; width: 100%; height: 100%; justify-content: center; align-items:baseline;'><p>Change your password</p>
            <div>
                <input name='change_pwd1' placeholder='type new password here*' style='border: 1px solid #7393B3; border-radius: 5px;' type='password' required='true'></input>
            </div>
            <div>
                <input name='change_pwd2' placeholder='type new password again*' style='border: 1px solid #7393B3; border-radius: 5px;' type='password' required='true'></input>
            </div>
            <div style='margin-top: 10%;'>
                <button style='background-color: white; color: white; background-color: #7393B3; border: 1px solid white; padding: 4px; border-radius: 5px;' type='submit'>Send</button>
            </div>
            <?php if ($print == true): ?>
              <div style="color: #88011f; font-weight: normal; font-size: 14px;">Something went wrong. Passwords must match and contain at least one letter, one uppercase letter, one number, one special char, no space and 8 to 16 characters.</div>
            <?php elseif ($start == false): ?>
              <div style="color: #055a36; font-weight: normal; font-size: 14px;">Your password has been changed</div>
            <?php endif; ?>
          </div>
        <div style='width: 100%; height: 100%; margin-left: 25px; background: repeat center/10% url(../src/images/cover.png);'></div>
    </form>
  </body>
  <footer></footer>
</html>