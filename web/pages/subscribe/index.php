
<?php
  require('../index.php');

  $visible = true;
  $weak_pass = true;
  $start = true;
  $info = array('name', 'surname', 'username');
  $complete = 3;
  for( $i = 0;$i < count($info);$i++ ) {
    if (isset($_POST[$info[$i]])) {
      if ((!strcmp($info[$i], 'username') && validate_input($_POST[$info[$i]], 1) == true) || validate_input($_POST[$info[$i]], 0) == true)
        $complete--;
    }
  }
  $pdo = connect();

  // If all required fields are fullfilled, proceed to form submission
  if (!$complete) {
    $start = false;
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['username'];
    $mdp = password_hash($_POST['password1'], PASSWORD_BCRYPT);

    $token = md5($email.time());

    // If both passwords match and meet requirements, activate profile via email.
    // Try and catch used here to avoid errors such as a non-unique key (email for example.)
    if (!(strcmp($_POST['password1'], $_POST['password2']))) {
      if (validate_input($_POST['password1'], 2) == true) {
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
        
          try {
            $sql = "INSERT INTO profile(uuid, firstname, surname, username, validpass, activated, notify, wallpaper, created_on, last_login) VALUES (?,?,?,?,?,?,?,?,?,?);";
            $pdo->prepare($sql)->execute([$uuid, $name, $surname, $email, $mdp, 0, true, NULL, date_format(date_create("now"), 'Y-M-d H:i:s'), date_format(date_create("now"), 'Y-M-d H:i:s')]);
            $ref = 'http://localhost:8080/activate/index.php';
            send_mail($email, "Hello from Camagru", "Activate your account", "<div>Please click on this link to activate your account: <a href=$ref>$token?activate=true</a></div>");
            $weak_pass = false;
          }
          catch(Exception $e) {

          }
        }
        $visible = false;
      }
  }
?>

<html dir="ltr" lang="en" style="font-family: system-ui; margin: 0; height: 100%; width: 100%;">
  <head>
    <meta charset="utf-8">
  </head>
  <body style="margin: 0; font-family: system-ui; width: 100%; height: 100%; background-color: #7393B3;">
    <div style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;">
    <div></div>
    <form action="" method="post" style="display: flex; flex-direction: column; align-items: baseline; color: white; gap: 0.5em; font-weight: normal;">
    <?php if ($visible == false && $weak_pass == false && $start == false): ?> 
      <p style="color: #055a36; font-weight: normal;">An email has been sent to your address to activate your account</p>
    <?php elseif ($start == false): ?>
      <p style="color: #88011f; font-weight: normal;">Something went wrong</p>
    <?php endif; ?>
    <div style="display: flex; flex-direction: column;">
      <p>First name*</p>
      <input style="border: 1px solid white; border-radius: 5px;" name='name' type='text' required='true'></input>
    </div>
    <div style="display: flex; flex-direction: column;">
      <p>Last name*</p>
      <input style="border: 1px solid white; border-radius: 5px;" name='surname' type='text' required='true'></input>
    </div>
    <div style="display: flex; flex-direction: column;">
      <p>Mail*</p>
      <input style="border: 1px solid white; border-radius: 5px;" name='username' type='email' required='true'></input>
    </div>
    <div style="display: flex; flex-direction: column; align-items: baseline;">
      <p>Password*</p>
      <input style="border: 1px solid white; border-radius: 5px;" name='password1' type='password' required='true'></input>
      <p style="font-size: 12px; font-weight: normal;">Password must contain at least one letter, one uppercase letter, one number, one special char, no space and 8 to 16 characters.</p>
    </div>
    <div style="display: flex; flex-direction: column;">
      <p>confirm password*</p>
      <input style="border: 1px solid white; border-radius: 5px;" name='password2' type='password' required='true'></input>
    <div>
    <div style="margin-top: 10%;"><button style="background-color: white; color: white;  color: #7393B3; background: white; border: 1px solid white; padding: 4px; border-radius: 5px;" type='submit'>Send</button></div>
  </form>
  <div></div>
  </body>
</html>