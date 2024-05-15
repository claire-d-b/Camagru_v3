<?php
  require ('../index.php');

  $pdo = connect();
  $existing = false;
  $start = true;

  if (isset($_POST['email_user']) && validate_input($_POST['email_user'], 1) == true):
    $start = false;
    $email = $_POST['email_user'];
    
    $uuid = uniqid('', true);
    // setcookie("cookies", $uuid, time()+60*60, '/');
    setcookie("cookies", $uuid, [
      'expires' => time() + 60*60,
      'path' => '/',
      'domain' => 'localhost',
      'secure' => true,
      'httponly' => true,
      'samesite' => 'Strict'
    ]);

    $sql_mail = "SELECT * FROM profile WHERE username = (?);";
    $data = $pdo->prepare($sql_mail);
    $data->bindParam(1, $email);
    $update_uuid = $data->fetch(\PDO::FETCH_ASSOC);

    if ($update_uuid):
      $name = $update_uuid['firstname'];
      $surname = $update_uuid['surname'];
      $mail = $update_uuid['username'];
      $validpass = $update_uuid['validpass'];
      $token = md5($email.time());
    endif;

    $sql = "UPDATE profile SET uuid=(?) WHERE username = (?);";
    $profile_set_id = $pdo->prepare($sql);
    $profile_set_id->bindParam(1, $uuid);
    $profile_set_id->bindParam(2, $email);
    $profile_set_id->execute();

    send_mail($mail, "Forgot password?",  "Reset your password", "<div>Please click on this link to reset your password: <a href='http://localhost:8080/password/index.php'>$token?activate=true</a></div>");

    $del_pass_req = "UPDATE profile  SET validpass=(?) WHERE username = (?);";
    $del_pass_data = $pdo->prepare($del_pass_req);
    $var = null;
    $del_pass_data->bindParam(1, $var);
    $del_pass_data->bindParam(2, $mail);
    $del_pass_data->execute();

    // header("Location: http://localhost:8080/login/index.php");
    $existing = true;
  endif;
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
    <form action="" method="post" style="color: #7393B3; font-family: system-ui; display: flex; width: 100%; height: 100%; justify-content: center; align-items: center;">
      <div style='width: 100%; height: 100%; margin-right: 25px; background: repeat center/10% url(../src/images/cover.png);'></div>
      <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: baseline; justify-content: center;">
          <p>Type your e-mail*</p>
          <input style="border: 1px solid #7393B3; border-radius: 5px;" name="email_user" type="email" required="true"></input>
          <div style="margin-top: 10px;"><button style="background-color: white; color: white; background-color: #7393B3; border: 1px solid white; padding: 4px; border-radius: 5px;" type="submit">Send</button></div>
          <?php if ($existing == false && $start == false): ?>
            <div style="font-weight: normal; padding-top: 10px; color: #88011f">E-mail not found</div>
          <?php elseif ($start == false): ?>
            <div style="font-weight: normal; padding-top: 10px; color: #055a36 ">An email has been sent to your address</div>
          <?php endif; ?>
        </div>
      <div style='width: 100%; height: 100%; margin-left: 25px; background: repeat center/10% url(../src/images/cover.png);'></div>
    </form>
  </body>
  <footer></footer>
  </html>
