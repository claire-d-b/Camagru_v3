
<?php
  require('../index.php');
  
  $wrong = 0;

  if (isset($_POST['validpass']) && isset($_POST['username'])
  && validate_input($_POST['username'], 1) == true
  && validate_input($_POST['validpass'], 2) == true) {
    $pdo = connect();
    $name = $_POST['username'];

    // Checks whether profile is activated (link in email has been clicked)
    $get_profile = "SELECT * FROM profile WHERE username = (?);";
    $datas_profile = $pdo->prepare($get_profile);
    $datas_profile->bindParam(1, $name);
    $datas_profile->execute();
    $data_profile = $datas_profile->fetch(\PDO::FETCH_ASSOC);

    // Current user has now a profile and can access to homepage with special rights.
    if (isset($data_profile['activated']) && $data_profile['activated']) {

      if (isset($data_profile['validpass'])) {
      $pass = $data_profile['validpass'];

      if (password_verify($_POST['validpass'], $pass)) {
        $uuid = uniqid('', true);
      //  setcookie("cookies", $uuid, time()+60*60, '/');
        setcookie("cookies", $uuid, [
          'expires' => time() + 60*60,
          'path' => '/',
          'domain' => 'localhost',
          'secure' => true,
          'httponly' => true,
          'samesite' => 'Strict'
      ]);
      
        $sql = "UPDATE profile SET uuid=(?) WHERE username = (?);";
        $profile_id = $pdo->prepare($sql);
        $profile_id->bindParam(1, $uuid);
        $profile_id->bindParam(2, $name);
        $profile_id->execute();
        header("Location: http://localhost:8080/home/index.php");
        // generer uuid à stocker dans la db doit etre changé à chaque connexion si le cookie existe la pers est connectée, ici genere un id de 23 char
        }
        else $wrong = 1;
      }
      else $wrong = 1;
    }
    else $wrong = 1;
  }
?>

<html dir="ltr" lang="en" style="font-family: system-ui; margin: 0; height: 100%; width: 100%;">
  <head>
    <meta charset="utf-8">
  </head>
  <body style="margin: 0; font-family: system-ui; width: 100%; height: 100%; background-color: #7393B3;">
    <div style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;">
      <form action="index.php" method="post" style="display: flex; flex-direction: column; color: white; gap: 0.5em;">
        <?php if ($wrong): ?>
          <div style="color: #88011f; font-weight: normal">Something went wrong</div>
        <?php endif; ?>
        <div style="display: flex; flex-direction: column;">
          <p>Enter your email*</p>
          <input style="border: 1px solid white; border-radius: 5px;" name="username" type="email" required="true"></input>
        </div>
        <div style="display: flex; flex-direction: column;">
          <p>Enter your password*</p>
          <input style="border: 1px solid white; border-radius: 5px;" name="validpass" type="password" required="true"></input>
        </div>
        <div style="display: flex; flex-direction: column;">
          <a href="http://localhost:8080/password/mail.php" style="text-decoration: none; color: white; font-size: 14px; font-weight: normal; border-radius: 5px;">Forgot password?</a>
        </div>
        <div style="margin-top: 10%;">
          <button style="background-color: white; color: white; color: #7393B3; background: white; border: 1px solid white; padding: 5px; border-radius: 4px;" type="submit">Send</button>
        </div>
      </form>
    </div>
  </body>
</html>