<!DOCTYPE html>
<?php

// error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);
// SECURITY :
// Control input
// Mark cookies as HTTPOnly and Secure to ensure they are not accessible via
// JavaScript and are only sent over secure protocols.
// The key to defending against HTML injection and XSS attacks is to sanitize and
// validate all user input and to encode output.
// Adopting these practices can significantly increase the security of your PHP applications

// Use Content Security Policy (CSP)
// Implementing Content Security Policy (CSP) headers can significantly reduce XSS risks. It helps in specifying which sources the browser should allow to load resources from. 

// Function to connect to the database
function connect () {
  // Safely get the value of an environment variable, ignoring whether 
  // or not it was set by a SAPI or has been changed with putenv
  // SAPI is an application programming interface (API) provided by the web server 
  // PHP has a direct module interface called SAPI for different web servers
  // getenv(?string $name = null, bool $local_only = false): string|array|false
  // params
  // name = Le nom de la variable en tant que chaîne de caractères ou null.
  // local_only = Lorsqu'il est défini sur true, seules les variables d'environnement locales
  // sont renvoyées, définies par le système d'exploitation ou putenv.
  // Cela n'a d'effet que lorsque name est une chaîne de caractères.
  // Valeur de retour : Retourne la valeur de la variable d'environnement name,
  // ou false si la variable d'environnement name n'existe pas. Si name est omit,
  // toutes les variables d'environnement sont retournée en tant qu'un tableau associatif. 
  $host = getenv('POSTGRES_HOST', true) ?: getenv('POSTGRES_HOST');
  $port = getenv('POSTGRES_PORT', true) ?: getenv('POSTGRES_PORT');
  $db = getenv('POSTGRES_DATABASE', true) ?: getenv('POSTGRES_DATABASE');
  $user = getenv('POSTGRES_USER', true) ?: getenv('POSTGRES_USER');
  $password = getenv('POSTGRES_PASSWORD', true) ?: getenv('POSTGRES_PASSWORD');

  try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$password";
    // make a database connection
    $pdo = new PDO($dsn, $user, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

  } catch (PDOException $e) {
      die($e->getMessage());
    }
  return $pdo;
}

// Returns true when current user is not logged
function is_not_logged () {
  if (isset($_COOKIE['cookies']))
    $cookie = $_COOKIE['cookies'];
  else $cookie = null;
  $pdo = connect();
  $sql = "SELECT activated FROM profile WHERE uuid = (?);";
  $data = $pdo->prepare($sql);
  $data->bindParam(1, $cookie);
  $data->execute();
  $get_activated = $data->fetch(\PDO::FETCH_ASSOC);
  if (isset($get_activated['activated']) && !$get_activated['activated'])
    return true;
  if (!isset($_COOKIE['cookies']))
    return true;
  return false;
}

// Checks whether cookies are set, in which case current user is allowed to access the page. Otherwise, logout is triggered.
function logout () {
  $cookie = $_COOKIE['cookies'];
  $pdo = connect();
  $sql = "SELECT activated FROM profile WHERE uuid = (?);";
  $data = $pdo->prepare($sql);
  $data->bindParam(1, $cookie);
  $data->execute();
  $get_activated = $data->fetch(\PDO::FETCH_ASSOC);
  if (isset($get_activated['activated']) && $get_activated['activated'] == 0)
    header("Location: http://localhost:8080/logout/index.php");
  if (!isset($_COOKIE['cookies']))
    header("Location: http://localhost:8080/logout/index.php");
}

// Function to create a web-readable string from an image to be stored in a database and used in an interface
// Def - base64 : Base64 is a binary to a text encoding scheme that represents binary data in an American Standard Code
// for Information Interchange (ASCII) string format.
// It's designed to carry data stored in binary format across the channels, and it takes any form of data
// and transforms it into a long string of plain text.
function createBase64FromImageResource($imgResource) {
  ob_start ( ); // The ob_start() function creates an output buffer.
  // A callback function can be passed in to do processing on the contents of the buffer before it gets flushed
  // from the buffer. Flags can be used to permit or restrict what the buffer is able to do.
  imagepng($imgResource);
  $imgData = ob_get_contents ( ); // ob_get_contents — Retourne le contenu du tampon de sortie
  ob_end_clean ( ); // Erase/clean the active output buffer content and disable that buffer.

  return base64_encode($imgData);
}

// Function to check whether the password as the parameter is in correct format
// Here we use preg_match : The preg_match() function returns whether a match/pattern was found in a string.
// If yes - return 1, if no - return 0, if error - return false
function meet_pass_requirements($pass) {
  if ((strlen($pass) >= 8 || strlen($pass) <= 16) &&
  preg_match("/[A-Z]/", $pass) && preg_match("/[a-z]/", $pass) && preg_match("/\W/", $pass) &&
  preg_match("/\d/", $pass)) return true;
  return false;
}

// trim Supprime les espaces (ou d'autres caractères) en début et fin de chaîne
// stripslashes Supprime les antislashs d'une chaîne
// htmlspecialchars Convert special characters to HTML entities
// if (!preg_match("/^[a-zA-Z ]*$/", $data)) { only letters and whitespaces
// type 0 = standard, type 1 = email, type 2 = password
function validate_input($str, $type) {
  $data = trim($str);
  if ($type == 2 && meet_pass_requirements($data) == false)
    return false;
  return true;
}

// Function that makes changes in the database when current user presses on the like button of a picture
function post_like($pdo, $user_id, $author, $id_picture, $date, $likename) {

  $sql = "SELECT * FROM likes WHERE author = (?) AND picture_id = (?);";
  $data = $pdo->prepare($sql);
  $data->bindParam(1, $author);
  $data->bindParam(2, $id_picture);
  $data->execute();
  $like = $data->fetch(\PDO::FETCH_ASSOC);
  if (isset($_POST[$likename])):
    if (isset($like['id'])):
    $reqDel = "DELETE FROM likes WHERE picture_id = (?);";
    $del = $pdo->prepare($reqDel);
    $del->bindParam(1, $id_picture);
    $del->execute();
    return 0; ?>
    <?php else:
      $reqInsert = "INSERT INTO likes(user_id, picture_id, author, created_on) VALUES (?,?,?,?);";
      $datass = $pdo->prepare($reqInsert);
      $datass->bindParam(1,  $user_id);
      $datass->bindParam(2, $id_picture);
      $datass->bindParam(3, $author);
      $datass->bindParam(4, $date);
      $datass->execute();
      return 1;
    endif; ?>
  <?php else:
    if (isset($like['id'])):
      return 1; ?>
    <?php else:
      return 0;
    endif;
  endif;
}

// Function that sends mail using smtp2go
function send_mail($email, $subject, $text_body, $html_body) {
  try {
    $base_url = "https://api.smtp2go.com/v3/";
    $send_email = $base_url.'email/send';
    $sender = getenv('CAMAGRU_MAIL', true) ?: getenv('CAMAGRU_MAIL');

    $content = array(
        "api_key" => "api-3D70AE744A6D11EEA2B9F23C91BBF4A0",
        "to" => ["<$email>"],
        "sender" => $sender,
        "subject" => $subject,
        "text_body" => $text_body,
        "html_body" => $html_body,
        "custom_headers" => [
          array(
            "header" => "Reply-To",
            "value" => $sender
          )
        ],
    );
    $data = json_encode($content);

    $options = array(
    'http' => array(
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => $data
    )
    );
    $context  = stream_context_create($options); // Creates and returns a stream context with any options
    // supplied in options preset. 
    $resp = file_get_contents($send_email, false, $context); // Sends an email with smtp2go
  } catch(Exception $e) {
  die($e->getMessage());
  }
}

// Function that updates the db with comments sent by current user on a specific picture
function post_comment($mail_author, $pdo, $user_id, $author, $id_picture, $date, $commentname, $sendcommentname) {

  if (isset($_POST[$sendcommentname]) && (!(empty($_POST[$commentname]))) &&
  ctype_space($_POST[$commentname]) == false && strcmp($_POST[$commentname], '')
  && validate_input($_POST[$commentname], 0) == true):
    $date = date_format(date_create("now"), 'Y-M-d H:i:s');
    $content = filter_var($_POST[$commentname], FILTER_SANITIZE_STRING);
    $reqComments = "INSERT INTO comments(user_id, author, picture_id, content, created_on) VALUES (?,?,?,?,?);";
    $data = $pdo->prepare($reqComments);
    $data->bindParam(1,  $user_id);
    $data->bindParam(2, $author);
    $data->bindParam(3, $id_picture);
    $data->bindParam(4, $content);
    $data->bindParam(5, $date);
    $data->execute();

    // sends email if notify option is set as true
    $picture_req = "SELECT * FROM picture WHERE id = (?);";
    $picture_res = $pdo->prepare($picture_req);
    $picture_res->bindParam(1, $id_picture);
    $picture_res->execute();

    $picture = $picture_res->fetch(\PDO::FETCH_ASSOC);
    $send_to = $picture['user_id'];

    $mail_req = "SELECT * FROM profile WHERE id = (?);";
    $mail_res = $pdo->prepare($mail_req);
    $mail_res->bindParam(1, $send_to);
    $mail_res->execute();

    $send_to_mail = $mail_res->fetch(\PDO::FETCH_ASSOC);
    $dest = $send_to_mail['username'];
    if ($send_to_mail['notify'] == true && $mail_author != $user_id):
      send_mail($dest, "New comment!",  "You have a new comment", "<div>$author has commented your picture:<div>".substr($content, 0, 100)."...</div></div>");
    endif;
  endif;
}

// Function that returns comments in the form of an array with key/value pairs at array[0], array[1] etc.
function fetch_comments($pdo, $id_picture) {
  $reqcomment = "SELECT * FROM comments WHERE picture_id = (?);";
  $data = $pdo->prepare($reqcomment);
  $data->bindParam(1, $id_picture);
  $data->execute();
  $comments = $data->fetchAll();
  return $comments;
}

// This function dislays comments with ability to delete them only when they have been created by current user
function display_comments($pdo, $id_picture, $userid) {
  $comments = fetch_comments($pdo, $id_picture);
  
  if ($comments && count($comments)): ?>
    <div style="display: flex; flex-direction: column; padding: 10px; width: 100%; height: auto; overflow-y: scroll; border-radius: 5px; gap: 0.5em; background-color: #B6D0E2;">

    <?php if (isset($userid) && isset($_POST['del_comment']) && isset($comments) && $comments[$_POST['del_comment']]['user_id'] == $userid):
      $del = $comments[$_POST['del_comment']]['id'];

      $reqDel = "DELETE FROM comments WHERE id = (?);";
      $data_del = $pdo->prepare($reqDel);
      $data_del->bindParam(1, $del);
      $data_del->execute();
      $comments = fetch_comments($pdo, $id_picture);
    endif; ?>

    <?php for( $j = 0; $j < count($comments); $j++ ):
      $delname = $j;

      if ($comments[$j]["picture_id"] == $id_picture): ?>

        <?php echo (is_not_logged() == false) ? '<div style="padding: 20px; align-self: center; gap: 0.5em; width: 100%; height: 100%;">' : '<div style="align-self: center; gap: 0.5em; width: 25%;">' ?>

          <?php $comment_author = (isset($userid) && $comments[$j]['user_id'] == $userid) ? 'you' : $comments[$j]['author']; ?>
            <form action='' method='post' style='display: flex; width: 100%; justify-content: space-between;'>
            <div style='font-size: 12px; border-radius: 5px; padding: 4px; border: 1px solid #7393B3; background: #7393B3; color: white;'><?php echo htmlspecialchars($comment_author, ENT_QUOTES, 'UTF-8'); ?></div>
          <?php if (isset($userid) && $comments[$j]['user_id'] == $userid): ?>
            <button name="del_comment" value=<?php echo $delname ?> type="submit">
              <svg xmlns="http://www.w3.org/2000/svg" height="1.25em" fill="#7393B3" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z"/></svg>
            </button>
          <?php endif; ?>
          </form>
          <?php $comment_content = $comments[$j]['content']; ?>
          <div style='margin-top: 5px; font-size: 12px; color: #7393B3; padding: 4px; border: 1px solid #7393B3; border-radius: 5px; overflow: scroll;'><?php echo htmlspecialchars($comment_content , ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
  <?php endif;
}

// The below html is displayed only when current user tries to access the index of the website
if (!(strcmp($_SERVER['REQUEST_URI'], '/')) || !(strcmp($_SERVER['REQUEST_URI'], '/index.php'))): ?>
  <html dir="ltr" lang="en" style="font-family: system-ui; margin: 0; height: 100%; width: 100%;">
    <head style="width: 0; height: 0;">
      <meta charset="utf-8">
    </head>
        <body style="margin: 0; font-family: system-ui; width: 100%; height: 100%; background-color: #7393B3;">
            <div style="display: flex; color: white; width: 100%; height: 100%; justify-content: center; align-items: center; gap: 1em;">
              <div><a style="text-decoration: none; color: white;" href="http://localhost:8080/login/index.php">Log in</a></div>
              <div>|</div>
              <div><a style="text-decoration: none; color: white;" href="http://localhost:8080/subscribe/index.php">Subscribe</a></div>
            </div>
        </body>
    <footer style="width: 0; height: 0;"></footer>
  </html>
<?php endif;

// prevent from accessing pages when not logged
if (strcmp($_SERVER['REQUEST_URI'], '/index.php') && strcmp($_SERVER['REQUEST_URI'], '/')
&& strcmp($_SERVER['REQUEST_URI'], '/login/index.php') && strcmp($_SERVER['REQUEST_URI'], '/login/') &&
strcmp($_SERVER['REQUEST_URI'], '/subscribe/index.php') && strcmp($_SERVER['REQUEST_URI'], '/subscribe/')
&& !isset($_COOKIE["cookies"])) {
  $sql = "UPDATE profile SET uuid=(?) WHERE uuid != (?);";
  $pdo = connect();
  $var = null;
  $u_profile = $pdo->prepare($sql);
  $u_profile->bindParam(1, $var);
  $u_profile->bindParam(2, $var);
  $u_profile->execute();
} ?>

<!-- Color codes for the website
#7393B3 blue gray
#B6D0E2 powder blue
#055a36 green 
#88011f red -->