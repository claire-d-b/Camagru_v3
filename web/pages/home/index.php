
<?php

  require('../index.php');
 
  $pdo = connect();
  $error = false;
  $wallpaper = '';
  $colors = -1;

  if (is_not_logged() == false):

    $uuid = $_COOKIE["cookies"]; // get current user info
    
    if ($uuid):
      $req = "SELECT * FROM profile WHERE uuid = (?)";
      $data_snap = $pdo->prepare($req);
      $data_snap->bindParam(1, $uuid);
      $data_snap->execute();
      if (($res = $data_snap->fetch(\PDO::FETCH_ASSOC)) != false):
        $user_id = $res['id'];
        $firstname = $res['firstname'];
        $lastname = $res['surname'];
        $author = $res['username'];
        $wallpaper = $res['wallpaper'];
        $date = date_format(date_create("now"), 'Y-M-d H:i:s');

        // Reset wallpaper at the beginning
        $del_wp_req = "UPDATE profile SET wallpaper=(?) where username = (?);";
        $del_wp = $pdo->prepare($del_wp_req);
        $null = null;
        $del_wp->bindParam(1, $null);
        $del_wp->bindParam(2, $author);
        $del_wp->execute();

        // verify if a file has been sent and is really a file
        if (isset($_POST['send_file']) && isset($_POST['chat_nb_file']) && !empty($_FILES['image']['tmp_name'][0])):
          if ($wallpaper):
            $wallp = file_get_contents($wallpaper);
            $bg = imagecreatefromstring($wallp);
  
          endif;
          $file_tmp = $_FILES['image']['tmp_name'][0];
          $imagedata = file_get_contents($file_tmp);

          if (($image = imagecreatefromstring($imagedata)) == false): // sets error when we are not dealing with a picture
              $error = true;
          endif;

          if ($error == false && $wallpaper):
            // Get the dimensions of the SVG image
            $svgWidth = imagesx($bg);
            $svgHeight = imagesy($bg);

            // Get the dimensions of the token image
            $imageWidth = imagesx($image);
            $imageHeight = imagesy($image);

            // Copy the PNG image onto the base image
            imagecopyresized($image, $bg, 0, 0, 0, 0, $imageWidth, $imageHeight, $svgWidth, $svgHeight); ?>
          <?php endif;
          if ($error == false):
            $final = createBase64FromImageResource($image); // base64 is created to be stored in the database
          
            $uuid = $_COOKIE["cookies"];

            // select current user and update 'user' in picture table
            $sql = "SELECT * FROM profile WHERE uuid = (?)";
            $data = $pdo->prepare($sql);
            $data->bindParam(1, $uuid);
            $data->execute();
            $get_profile = $data->fetch(\PDO::FETCH_ASSOC);
            $username = $get_profile['id'];
            $author = $get_profile['firstname'];
            $date = date_format(date_create("now"), 'Y-M-d H:i:s');
            $title = "picture_".$username;

            // insert picture with relevant information
            $req = "INSERT INTO picture(title, user_id, author, img, created_on) VALUES (?,?,?,?,?)";
            $res = $pdo->prepare($req);
            $res->bindParam(1, $title);
            $res->bindParam(2, $username);
            $res->bindParam(3, $author);
            $res->bindParam(4, $final);
            $res->bindParam(5, $date);
            $res->execute();
          endif;
        endif;

        // verify if snapshot has been sent
        if (isset($_POST['snapshot_id']) && isset($_POST['chat_nb_snapshot']) && $wallpaper):

          $photo = $_POST['snapshot_id'];

          $imagedata1 = file_get_contents($wallpaper);
          $imagedata2 = file_get_contents($photo);
          $image1 = imagecreatefromstring($imagedata1);
          $image2 = imagecreatefromstring($imagedata2);

          // Get the dimensions of the SVG image
          $svgWidth = imagesx($image1);
          $svgHeight = imagesy($image1);

          // Get the dimensions of the token image
          $imageWidth = imagesx($image2);
          $imageHeight = imagesy($image2);

          // Copy the PNG image onto the base image
          imagecopyresized($image2, $image1, 0, 0, 0, 0, $imageWidth, $imageHeight, $svgWidth, $svgHeight);

          $final = createBase64FromImageResource($image2);

          $uuid = $_COOKIE["cookies"];

          // select current user and update 'user' in picture table
          $sql = "SELECT * FROM profile WHERE uuid = (?)";
          $data = $pdo->prepare($sql);
          $data->bindParam(1, $uuid);
          $data->execute();
          $get_profile = $data->fetch(\PDO::FETCH_ASSOC);
          $username = $get_profile['id'];
          $author = $get_profile['firstname'];
          $date = date_format(date_create("now"), 'Y-M-d H:i:s');
          $title = "picture_".$username;

          // insert picture with relevant information
          $req = "INSERT INTO picture(title, user_id, author, img, created_on) VALUES (?,?,?,?,?)";
          $res = $pdo->prepare($req);
          $res->bindParam(1, $title);
          $res->bindParam(2, $username);
          $res->bindParam(3, $author);
          $res->bindParam(4, $final);
          $res->bindParam(5, $date);
          $res->execute();
        endif;
      endif;
    endif;
  endif;

  // Below we fetch all users pictures
  $sql = "SELECT * FROM picture"; // get array of pictures
  $data = $pdo->prepare($sql);
  $data->execute();
  $pictures = $data->fetchAll();
?>

<html dir="ltr" lang="en" style="font-family: system-ui; margin: 0; height: 100%; width: 100%;">
  <head>
    <?php if (is_not_logged() == false):
      // here is the script that updates html values with pictures' base64 strings, readable by the web page ?>
      <script type="text/javascript" src="photo.js"></script>
    <?php endif; ?>
    <div style="display: flex; justify-content: center; align-items: center; width: 100%; background: white; height: 40px; position: fixed; top: 0px; box-shadow: 0px 2px 2px #808b96;">
      <meta charset="utf-8">
      <?php  if (is_not_logged() == false): ?>
        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 20px;">
          <div style="display: flex; gap: 0.5em;">
            <form action="../home/index.php">
              <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" height="1.25em" fill="#7393B3" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z"/></svg>
              </button>
            </form>
            <form action="../profile/index.php">
              <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" height="1.25em" fill="#7393B3" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z"/></svg>
              </button>
            </form>
          </div>
        </div>
      <?php endif; ?>
      <div style="font-size: 18px; color: #7393B3; width: 100%; text-align: right; padding-right: 15px;">Camagru</div>
      </div>
    </div>
  </head>
  <body id="camera-body" echo <?php (is_not_logged() == false) ?
  'style="margin: 0; font-family: system-ui; width: 100%; height: 100%; overflow: hidden;"'
  : 'style="margin: 0; font-family: system-ui; width: 100%; height: 100%; overflow: scroll;"' ?> >
    <?php if (is_not_logged() == false): ?>
      <div id="container" style="display: flex; flex-direction: column; width: 100%; height: 100%;">
        <div style="display: flex; flex-direction: row; width: 100%; height: 100%;">

          <?php // Here we can upload a file ?>
          <div style="display: flex; flex: 2; height: 100%; flex-direction: column;">
            <div style="flex: 1; display: flex; flex-direction: column; height: 100%;">
              <div style="display: flex; flex-direction: column; height: 100%; padding-top: 40px; padding-bottom: 50px;">
              <form action="index.php" method="post" enctype="multipart/form-data" style="display: flex; flex: 1; flex-direction: column; color: white; background-color: #7393B3; justify-content: center">
                <div style="display: flex; justify-content: center; align-items: center;">
                  <div style="color: white; padding: 10px;">
                    <div style="display: flex; gap: 1em;">
                      <div>Select image to upload:</div>
                      <input type="file" name="image[]">
                      <input id="chat_nb_file" name="chat_nb_file" type="text" value="" style="display: none" />
                    </div>
                    <div>
                      <input type="submit" name="send_file" value="Send" style="border: 1px solid white; color: white; background-color: #7393B3; padding: 4px; border-radius: 5px; box-shadow: 0px 2px 2px #B6D0E2;" />
                    </div>
                  </div>
                </div>
              </form>
              <?php if ($error == true): ?>
                <div style='display: flex; width: 100%; justify-content: center; color: #88011f; font-size: 14px;'>Unable to upload file</div>
              <?php endif; ?>

              <?php // Here we have the camera component ?>
              <div style="display: flex; color: white; height: 100%; width: 100%; background: repeat center/5% url(../src/images/cover.png);">
                <div style="display: flex; flex: 2; flex-direction: column; gap: 1.5em; width: 100%; height: 100%;">

                  <div class="camera" style="display: flex; flex-direction: column; gap: 0.5em; height: 100%; width: 100%;">
                    <div style="display: flex; flex-direction: column; gap: 0.5em; justify-content: center; align-items: center; padding: 5em;">
                      <div style="display: flex; flex-direction: column; gap: 0.5em;">
                        <video style="border-radius: 10px; color: #88011f;" id="video">Video not available.</video>
                        <div>
                          <button id="startbutton" style="border: 1px solid white; color: white; background: #7393B3; padding: 4px; border-radius: 5px; box-shadow: 0px 2px 2px #808b96;">Take snapshot</button>
                        </div>
                      </div>
                      <form action="index.php" method="post">
                        <div style="display: flex; flex-direction: column; gap: 0.5em; justify-content: center; align-items: start;">
                            <input id="snapshot_id" name="snapshot_id" type="text" value="" style="display: none" />
                            <input id="chat_nb_snapshot" name="chat_nb_snapshot" type="text" value="" style="display: none" />
                          <canvas style="border-radius: 10px;" id="canvas"></canvas>
                        <div><button type="submit" <?php echo (!isset($_POST['chosen'])) ?
                      'style="border: 1px solid white; color: #808b96; background-color: white; padding: 4px; border-radius: 5px; box-shadow: 0px 2px 2px #B6D0E2;"'
                      : 'style="border: 1px solid white; color: white; background-color: #7393B3; padding: 4px; border-radius: 5px; box-shadow: 0px 2px 2px #B6D0E2;"'; if (!isset($_POST['chosen'])): echo "disabled=true"; endif; ?> >Send</button></div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <?php // here is the list of pictures current user can choose to be superposed ?>
                <div style="height: 75%; padding-bottom: 25%; flex: 1; overflow: scroll; background-color: #B6D0E2;">
                  <div style="gap: 1em; justify-content: center; align-items: center; display: flex; flex-direction: column;">
                    <?php for ($i = 0; $i < 8; $i++):
                        $source[$i] = '../src/images/chat_' . $i+1 . '.png';
                    endfor; ?>
                    <div style='display: flex; flex-direction: column; align-items: baseline; align-items: baseline; padding: 15px;'>
                    <?php
                    for ($i = 0; $i < 8; $i++):
                      // update background
                      if (isset($_POST['chosen'])):
                        $colors = $i;
                        $wallpaper = $_POST['chosen'];

                        $chosen_req = "UPDATE profile SET wallpaper=(?) WHERE username = (?);";
                        $chosen_data = $pdo->prepare($chosen_req);
                        $chosen_data->bindParam(1, $wallpaper);
                        $chosen_data->bindParam(2, $author);
                        $chosen_data->execute();
                      endif; ?>

                      <form action="../home/index.php" method="post" style="height: 100%; width: 100%;">
                        <div><img style='border-radius: 10px' width='150px' height='120px' src='<?php echo $source[$i] ?>' /></div>
                        <?php if ($wallpaper != $source[$i]): // checks whether we are dealing with chosen wallpaper or not (here: not) ?>
                        <button type="submit" name="chosen" value=<?php echo $source[$i] ?> onclick='setBase64Image(`<?php echo $source[$i] ?>`)' style='background-color: transparent; color: white; padding: 4px; border: 1px solid white; border-radius: 5px; box-shadow: 0px 2px 2px #808b96;'>Choose</button>
                        <?php else: ?>
                        <button type="submit" name="chosen" value=<?php echo $source[$i] ?> onclick='setBase64Image(`<?php echo $source[$i] ?>`)' style='background-color: #7393B3; color: white; padding: 4px; border: 1px solid white; border-radius: 5px; box-shadow: 0px 2px 2px #808b96;'>Choose</button>
                        <?php endif; ?>
                    </form>
                    <?php endfor; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif;
      echo ((is_not_logged() == false)) ?
      '<div style="width: 25%; height: 80%;">
        <div style="display: flex; align-items: center; flex-direction: column; overflow: scroll; height: 100%; width: 80%; padding-bottom: 40px; padding-top: 40px; padding-left: 20px; padding-right: 20px;">'
      : '<div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding-top: 40px; padding-bottom: 40px;">
      <div style="gap: 2em; overflow: scroll; height: 100%; width: 100%; padding-left: 20px; padding-right: 20px;">';
    
      // Below we display all users pictures with ability to like and comment them
      if (isset($pictures) && count($pictures)):
        for( $i = 0; $i < count($pictures); $i++ ):
          $id_picture = $pictures[$i]['id'];
          $name = $pictures[$i]['author'];
          $mail_author = $pictures[$i]['user_id'];
          $created = $pictures[$i]['created_on'];

          if (is_not_logged() == false):
            $delname = 'del'.$i;

            $likename = "likelike".$i;
            $commentname = "comment".$i;
            $sendcommentname = "sendcomment".$i;

            $like = post_like($pdo, $user_id, $firstname, $id_picture, $date, $likename);
            post_comment($mail_author, $pdo, $user_id, $firstname, $id_picture, $date, $commentname, $sendcommentname); ?>
            <div style="display: flex; flex-direction: column; gap: 1em; align-items: center; width: 100%;">

            <?php if (isset($_POST[$delname])): // if a specific picture is deleted by current user
              $pdo = connect();
              $del_comments = "DELETE FROM comments USING picture WHERE picture_id = (?);"; // delete comments' request on that specific picture
              $delco = $pdo->prepare($del_comments);
              $delco->bindParam(1, $id_picture);
              $delco->execute();
              $del_req = "DELETE FROM picture WHERE id = (?);"; // delete the picture after having deleted its comments
              $del = $pdo->prepare($del_req);
              $del->bindParam(1, $id_picture);
              $del->execute();
              $get_picture = "SELECT * FROM picture";
              $data = $pdo->prepare($get_picture);
              $data->execute();
              $pictures = $data->fetchAll(); // get all pictures after deletion
            endif; ?>
            </div>
          <?php endif;

          if (isset($pictures[$i]['img'])):
            if (is_not_logged() == false):

              if (isset($pictures[$i]['user_id']) && $pictures[$i]['user_id'] == $user_id): // checks whether the picture has been created by current user and add trash button if so ?>
                <form action="" method="post" style="width: 100%; display: flex; align-items: baseline; justify-content: space-between;">
                  <div style="color: #7393B3; font-weight: normal; font-size: 12px; padding-top: 15px; padding-bottom: 10px;">Created by you on <?php echo htmlspecialchars($created, ENT_QUOTES, 'UTF-8'); ?></div>
                  <button name=<?php echo $delname?> type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" height="1.25em" fill="#7393B3" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z"/></svg>
                  </button>
                </form>
              <?php else: ?>
                <div style="display: flex; justify-content: center; color: #7393B3; font-weight: normal; font-size: 14px; padding-top: 15px; padding-bottom: 10px;">Created by <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?> on <?php echo htmlspecialchars($created, ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
            <?php else: ?>
              <div style="display: flex; justify-content: center; color: #7393B3; font-weight: normal; font-size: 14px; padding-top: 15px; padding-bottom: 10px;">Created by <?php  echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?> on <?php echo htmlspecialchars($created, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
              
            <div style="display: flex; flex-direction: column; gap: 0.5em; width: 100%; align-items: center; justify-content: center;">
              <?php // echo $pictures[$i]['img'] ?>
              <img style="border-radius: 10px;" width="200px" height="150px" src="data:image/png;base64,<?php echo $pictures[$i]['img'] ?>" />
              <?php $nb = $i+1; echo "<div style='width: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 5px; color: #7393B3; font-weight: normal; font-size: 14px;'>$nb" ?>
            <!-- </div> -->
            <?php if (is_not_logged() == false): ?>
              <div style="align-self: flex-end;">
                <form action="" method="post" style="display: flex; justify-content: center;">
                  <button name=<?php echo $likename ?> type="submit" style="border: 1px solid #7393B3; border-radius: 5px; padding: 4px; display: flex; justify-content: center;">
                  <?php if (!$like): ?>
                    <svg fill="#7393B3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path id="likePath" d="M225.8 468.2l-2.5-2.3L48.1 303.2C17.4 274.7 0 234.7 0 192.8v-3.3c0-70.4 50-130.8 119.2-144C158.6 37.9 198.9 47 231 69.6c9 6.4 17.4 13.8 25 22.3c4.2-4.8 8.7-9.2 13.5-13.3c3.7-3.2 7.5-6.2 11.5-9c0 0 0 0 0 0C313.1 47 353.4 37.9 392.8 45.4C462 58.6 512 119.1 512 189.5v3.3c0 41.9-17.4 81.9-48.1 110.4L288.7 465.9l-2.5 2.3c-8.2 7.6-19 11.9-30.2 11.9s-22-4.2-30.2-11.9zM239.1 145c-.4-.3-.7-.7-1-1.1l-17.8-20c0 0-.1-.1-.1-.1c0 0 0 0 0 0c-23.1-25.9-58-37.7-92-31.2C81.6 101.5 48 142.1 48 189.5v3.3c0 28.5 11.9 55.8 32.8 75.2L256 430.7 431.2 268c20.9-19.4 32.8-46.7 32.8-75.2v-3.3c0-47.3-33.6-88-80.1-96.9c-34-6.5-69 5.4-92 31.2c0 0 0 0-.1 .1s0 0-.1 .1l-17.8 20c-.3 .4-.7 .7-1 1.1c-4.5 4.5-10.6 7-16.9 7s-12.4-2.5-16.9-7z"/></svg>
                  <?php else: ?>
                    <svg fill="#7393B3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path id="likePath" d="M47.6 300.4L228.3 469.1c7.5 7 17.4 10.9 27.7 10.9s20.2-3.9 27.7-10.9L464.4 300.4c30.4-28.3 47.6-68 47.6-109.5v-5.8c0-69.9-50.5-129.5-119.4-141C347 36.5 300.6 51.4 268 84L256 96 244 84c-32.6-32.6-79-47.5-124.6-39.9C50.5 55.6 0 115.2 0 185.1v5.8c0 41.5 17.2 81.2 47.6 109.5z"/></svg>
                  <?php endif; ?>
                  </button>
                </form>
              </div>
              <form action="" method="post" style="width: 100%; display: flex; flex-direction: column; gap: 1em;">
                <input name=<?php echo $commentname ?> type="text" placeholder="Type your comment here..." style="width: 100%; border-radius: 5px; border: 1px solid #7393B3;">
                <div><button name=<?php echo $sendcommentname ?> type="submit" style="color: white; padding: 4px; border: 1px solid #7393B3; color: #7393B3; background: white; border-radius: 5px; box-shadow: 0px -2px 3px #808b96;">submit</button></input></div>
              </form>
            <?php endif;
          endif; ?>
          <?php if (isset($user_id)): display_comments($pdo, $id_picture, $user_id); else: display_comments($pdo, $id_picture, null); endif; ?>
          </div>
        </div>
        <?php endfor; ?>
      <?php else: ?>
        <div style="width: 100%; display: flex; align-items: center; justify-content: space-between; color: #7393b3;">No picture available.</div>
      <?php endif; ?>
      </div></div>
      </div>
    </div>
  </body>
  <?php if (is_not_logged() == false): ?>
  <footer style="height: 50px; position: fixed; bottom: 0px; margin: 0; background-color: white; width: 100%; display: flex; align-items: center; justify-content: center; box-shadow: 0px -2px 3px #808b96;">
    <a style="background-color: #7393B3; text-decoration: none; color: white; border: 1px solid white; border-radius: 5px; padding: 4px;" href="http://localhost:8080/logout/index.php">Log out</a>
  </footer>
  <?php else: ?>
    <footer style="height: 50px; position: fixed; bottom: 0px; margin: 0; background-color: white; width: 100%; display: flex; align-items: center; justify-content: center; box-shadow: 0px -2px 3px #808b96;"></footer>
  <?php endif; ?>
</html>