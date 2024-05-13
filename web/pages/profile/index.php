<?php
    require ('../index.php');

    logout();

    $uuid = $_COOKIE["cookies"];

    $pdo = connect();

    $get_profile = "SELECT * FROM profile WHERE uuid = (?)";
    $data = $pdo->prepare($get_profile);
    $data->bindParam(1, $uuid);
    $data->execute();
    $profile = $data->fetch(\PDO::FETCH_ASSOC);

    $name = $profile['firstname'];
    $surname = $profile['surname'];
    $mail = $profile['username'];
    $validpass = $profile['validpass'];
    $activated = $profile['activated'];
    $notif = $profile['notify'];

    $error = false;
    $start = true;
    $sql = '';
    $notif_bool = $notif;

    try {
        // As soon as submit button is pressed, check if all required fields are correctly filled.
        // Check email address via mail confirmation.
        // Sets whether the user wants to be notified as his picture is commented or not.
        if (isset($_POST['sending'])):

        if (isset($_POST['change_fn']) && ctype_space($_POST['change_fn']) == false
        && strcmp($_POST['change_fn'], '') && validate_input($_POST['change_fn'], 0) == true):
            $new = $_POST['change_fn'];
            $sql = "UPDATE profile SET firstname=(?) WHERE uuid = (?)";
            $req = $pdo->prepare($sql);
            $req->bindParam(1, $new);
            $req->bindParam(2, $uuid);
            $req->execute();
            $start = false;
            header('Refresh: 0');

        endif;
        if (isset($_POST['change_sn']) && ctype_space($_POST['change_sn']) == false
        && strcmp($_POST['change_sn'], '') && validate_input($_POST['change_sn'], 0) == true):
            $new = $_POST['change_sn'];
            $sql = "UPDATE profile SET surname=(?) WHERE uuid = (?)";
            $req = $pdo->prepare($sql);
            $req->bindParam(1, $new);
            $req->bindParam(2, $uuid);
            $req->execute();
            $start = false;
            header('Refresh: 0');

        endif;
        if (isset($_POST['change_mail']) && ctype_space($_POST['change_mail']) == false
        && strcmp($_POST['change_mail'], '') && validate_input($_POST['change_mail'], 1) == true):
            $new = $_POST['change_mail'];
            $get_mail_req = "SELECT username FROM profile WHERE uuid = (?);";
            $get_mail = $pdo->prepare($get_mail_req);
            $get_mail->bindParam(1, $uuid);
            $get_mail->execute();
            $mail_data = $get_mail->fetch(\PDO::FETCH_ASSOC);
            if (strcmp($mail_data['username'], $new)):
                $sql = "UPDATE profile SET username=(?) WHERE uuid = (?)";
                $req_pass = $pdo->prepare($sql);
                $req_pass->bindParam(1, $new);
                $req_pass->bindParam(2, $uuid);
                $req_pass->execute();
                $unset_activated = "UPDATE profile SET activated=(?) WHERE uuid = (?)";
                $req_act = $pdo->prepare($unset_activated);
                $zero = 0;
                $req_act->bindParam(1, $zero);
                $req_act->bindParam(2, $uuid);
                $req_act->execute();

                $token = $uuid;
                $ref = 'http://localhost:8080/activate/index.php';
                send_mail($new, "Email verification", "Confirm your email", "<div>Please click on this link confirm your email: <a href=$ref>$token?activate=true</a></div>");
                $start = false;
            endif;
            header('Refresh: 0');

        endif;

        if (isset($_POST['notify'])):
            $notif_bool = 't'; ?>
        <?php else:
            $notif_bool = 'f';
        endif;
        $notified = "UPDATE profile SET notify=(?) WHERE uuid = (?);";
        $set_notify = $pdo->prepare($notified);
        $set_notify->bindParam(1, $notif_bool);
        $set_notify->bindParam(2, $uuid);
        $set_notify->execute();
        $start = false;

        // ctype space return value : false if empty string or string that contains characters that are different from spaces.
        // Allows to check whether a string is only made of spaces. In addition to strcmp, which allows for comparison
        // (empty string vs parameter string)
        // Here we chack old vs new password and change password if no error arises.
        if (isset($_POST['change_pwd2']) && isset($_POST['change_pwd1']) && isset($_POST['change_pwd0']) &&
        ctype_space($_POST['change_pwd2']) == false && ctype_space($_POST['change_pwd1']) == false && ctype_space($_POST['change_pwd0']) == false
        && strcmp($_POST['change_pwd2'], '') && strcmp($_POST['change_pwd1'], '') && strcmp($_POST['change_pwd0'], '')
        && (!(strcmp($_POST['change_pwd1'], $_POST['change_pwd2'])))
        && validate_input($_POST['change_pwd0'], 2) == true
        && password_verify($_POST['change_pwd0'], $validpass)):
            $new_pass = password_hash($_POST['change_pwd2'], PASSWORD_BCRYPT);
            $sql = "UPDATE profile SET validpass=(?) WHERE uuid = (?)";
            $req_pwd = $pdo->prepare($sql);
            $req_pwd->bindParam(1, $new_pass);
            $req_pwd->bindParam(2, $uuid);
            $req_pwd->execute();
            $start = false;
        elseif (isset($_POST['change_pwd2']) && isset($_POST['change_pwd1']) && isset($_POST['change_pwd0']) &&
        ctype_space($_POST['change_pwd2']) == false && ctype_space($_POST['change_pwd1']) == false && ctype_space($_POST['change_pwd0']) == false
        && strcmp($_POST['change_pwd2'], '') && strcmp($_POST['change_pwd1'], '') && strcmp($_POST['change_pwd0'], '')):
            $error = true; $start = false; ?>
        <?php else: $start = false;
        endif;
    endif;
    } catch (PDOException $e) {
        $error = true;
} ?>

<html dir='ltr' lang='en' style='font-family: system-ui; margin: 0; height: 100%; width: 100%;'>
    <head>
        <div style='display: flex; justify-content: center; align-items: center; width: 100%; background: white; height: 40px; position: fixed; top: 0px; box-shadow: 0px 2px 2px #808b96;'>
        <meta charset='utf-8'>
        <div style='display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 20px;'>
            <div style='display: flex; gap: 0.5em;'><form action='../home/index.php'>
            <button type='submit'>
                <svg xmlns='http://www.w3.org/2000/svg' height='1.25em' fill='#7393B3' viewBox='0 0 448 512'><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d='M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z'/></svg>
            </button>
            </form>
            <form action='../profile/index.php'>
            <button type='submit'>
                <svg xmlns='http://www.w3.org/2000/svg' height='1.25em' fill='#7393B3' viewBox='0 0 448 512'><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d='M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z'/></svg>
            </button>
            </form></div>
            <div style="font-size: 18px; color: #7393B3; width: 100%; text-align: right; padding-right: 15px;">Camagru</div>
        </div>
        </div>
    </head>
    <body style='margin: 0; font-family: system-ui; width: 100%; height: 100%; overflow: hidden;'>
        <form action='index.php' method='post' style='display: flex; flex-direction: column; font-family: system-ui; height: 100%; width: 100%;'>
            <div style='display: flex; justify-content: center; width: 100%; height: 100%;'>
                <div style='width: 100%; height: 100%; margin-right: 25px; background: repeat center/10% url(../src/images/cover.png);'></div>
                <div style='color: #7393B3; display: flex; flex-direction: column; width: 100%; height: 100%; justify-content: center; align-items: center;'>
                    <div style='align-self: start; width: 100%;'>
                        <p style='width: 100%;'>First name</p>
                        <div style='width: 100%;'>
                            <input name='change_fn' placeholder=<?php echo $name ?> value=<?php echo $name ? $name : '' ?> style='width: 100%; border: 1px solid #7393B3; border-radius: 5px;' type='text'></input>
                        </div>
                    </div>
                    <div style='align-self: start; width: 100%;'>
                        <p style='width: 100%;'>Last name</p>
                        <div style='width: 100%;'>
                            <input name='change_sn' placeholder=<?php echo $surname ?> value=<?php echo $surname ? $surname : '' ?> style='width: 100%; border: 1px solid #7393B3; border-radius: 5px;' type='text'></input>
                        </div>
                    </div>
                    <div style='align-self: start; width: 100%;'>
                        <p style='width: 100%;'>Mail</p>
                        <div style='width: 100%;'>
                            <input name='change_mail' placeholder=<?php echo $mail ?> value=<?php echo $mail ? $mail : '' ?> style='width: 100%; border: 1px solid #7393B3; border-radius: 5px;' type='email'></input>
                        </div>
                    </div>
                    <div style='align-self: start; width: 100%;'>
                    <?php // Note : À la différence des autres champs, les valeurs des cases à cocher ne sont envoyées au serveur
                    // que lorsqu'elles sont cochées. Lorsque c'est le cas, c'est la valeur de l'attribut value qui est envoyé
                    // (ou la valeur on si aucun attribut value n'est présent). À la différence des autres navigateurs,
                    // Firefox conserve l'état coché placé dynamiquement d'un champ <input> après les rechargements de la page.
                    // L'attribut autocomplete peut être utilisé afin de contrôler cette fonctionnalité. ?>
                        <p style='width: 100%; font-weight: normal; font-size: 14px;'>Do you want to be notified when someone comment your picture?</p>
                        <input type='checkbox' name='notify' autocomplete='off' <?php echo ($notif_bool == 't') ? 'checked=true' : null; ?> />
                    </div>
                    <div style='display: flex; flex-direction: column; align-self: start; width: 100%; gap: 0.5em;'><p>Change your password</p>
                        <div style='width: 100%;'>
                            <input name='change_pwd0' placeholder='Type old password here' style='width: 100%; border: 1px solid #7393B3; border-radius: 5px;' type='password'></input>
                        </div>
                        <div style='width: 100%;'>
                            <input name='change_pwd1' placeholder='Type new password here' style='width: 100%; border: 1px solid #7393B3; border-radius: 5px;' type='password'></input>
                        </div>
                        <div style='width: 100%;'>
                            <input name='change_pwd2' placeholder='Type new password again' style='width: 100%; border: 1px solid #7393B3; border-radius: 5px;' type='password'></input>
                        </div>
                        <div style='display: flex; margin-top: 10%; width: 100%; justify-content: end;'>
                            <input name="sending" value="Update" style='background-color: white; color: white; background-color: #7393B3; border: 1px solid #7393B3; padding: 4px; border-radius: 5px;' type='submit' />
                        </div>
                        <?php if ($error == true): ?>
                            <div style="color: #88011f; font-weight: normal;">Something went wrong</div> 
                        <?php elseif ($error == false && $start == false): ?> 
                            <div style="color: #055a36; font-weight: normal;">Your data have been changed.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <div style='width: 100%; height: 100%; margin-left: 25px; background: repeat center/10% url(../src/images/cover.png);'></div>
        </form>
    </body>
    <footer style='height: 50px; position: fixed; bottom: 0px; background-color: white; width: 100%; display: flex; align-items: center; justify-content: center; box-shadow: 0px -2px 3px #808b96;'>
        <a style='background-color: #7393B3; text-decoration: none; color: white; border: 1px solid white; border-radius: 5px; padding: 4px;' href='http://localhost:8080/logout/index.php'>Log out</a>
    </footer>
</html>