<?php

require_once 'php/login.php';
require_once 'php/tools.php';

session_start();
validate_session();

if (is_logged_in())
{
    echo '<script>window.location.href = "php/main.php";</script>';
}
else
{
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die(mysql_fatal_error());
    create_users_table($conn);

    $success = false;
    if (isset($_POST['login']))
    {
        $success = login($conn);
    }

    print_html();
    if (isset($_POST['login']) && !$success)
    {
        echo '<h5 class="text-center pb-5">Invalid username/password, please try again.</h5>';
    }

    $conn->close();
}

function print_html()
{
    echo <<<_END
    <html>
    <head>
      <link rel="icon" href="assets/favicon.png">
      <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
      <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
      <link href="css/styles.css" rel="stylesheet">
      <title>Crypto Online</title>
    </head>

    <body>
      <div class="container pt-5">
        <div class="row text-center">
          <div class="col">
            <h1 class="mb-4">Crypto Online</h1>
            <h5 class="mb-4">Web application for encrypting/decrypting from text input or text file.<br>Available ciphers: Simple Substitution, Double Transposition, and RC4.</h5>
            <form method="post" action="index.php">
              <div class="form-group">
                <input placeholder="Username" type="text" name="username" required>
              </div>
              <div class="form-group">
                <input placeholder="Password" type="text" name="password" required>
              </div>
              <input class="btn btn-primary btn-lg" type="submit" name="login" value="LOGIN">
            </form>
            <p>Or <a href="php/register.php">register</a>.</p>
          </div>
        </div>
      </div>
    </body>
    </html>
_END;
}

function login($conn)
{
    $username = mysql_entities_fix_string($conn, $_POST['username']);
    $password = mysql_entities_fix_string($conn, $_POST['password']);
    
    $query = 'SELECT password_encrypted, salt FROM users WHERE username=?';
    $stmt = $conn->prepare($query);
    if (!$stmt) die(mysql_fatal_error());
    
    $result = $stmt->bind_param('s', $username);
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->execute();
    if (!$result) die(mysql_fatal_error());
    
    $result = $stmt->bind_result($password_encrypted, $salt);
    if (!$result) die(mysql_fatal_error());

    $success = false;
    if ($stmt->fetch())
    {
        $pwd_encrypted = hash('ripemd128', "$salt$password");
    
        if ($pwd_encrypted == $password_encrypted)
        {
            $_SESSION['username'] = $username;
            $_SESSION['check'] = get_check_hash();
            echo '<script>window.location.href = "php/main.php";</script>';
            $succes = true;
        }
    }
    
    $stmt->free_result();
    $stmt->close();
    return $success;
}

?>
